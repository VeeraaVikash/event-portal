<?php
require_once 'includes/workflow.php';
require_once 'includes/db.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php?modal=login");
    exit;
}

$user_id = $_SESSION["id"];
$edit_data = [];
$form_error = null;

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $is_update = isset($_GET['id']) && is_numeric($_GET['id']);

    if(!ec_csrf_valid()) {
        http_response_code(403);
        exit('Invalid or missing security token. Please reload the page and try again.');
    }

    // 1. Core Proposal
    $title = trim($_POST['title'] ?? '');
    $desc = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $past_events = $_POST['past_events'] ?? null;
    $other_details = $_POST['other_details'] ?? null;
    $total_pax = intval($_POST['total_participants'] ?? 0);
    $part_cats = isset($_POST['part_categories']) ? implode(', ', $_POST['part_categories']) : '';
    $stud_cats = isset($_POST['student_categories']) ? implode(', ', $_POST['student_categories']) : '';

    // Handle specific checkbox name formatting fix internally
    $part_cats = str_replace('Students (Category)', 'Students', $part_cats);

    // Validate before writing anything.
    $errors = [];
    if($title === '') {
        $errors[] = 'Title is required';
    }
    if(!ec_valid_date($start_date) || !ec_valid_date($end_date)) {
        $errors[] = 'Valid start and end dates are required';
    } elseif($end_date < $start_date) {
        $errors[] = 'End date cannot be before start date';
    }
    if($total_pax < 0) {
        $errors[] = 'Participant count cannot be negative';
    }
    if($errors) {
        http_response_code(400);
        exit('Could not save proposal: ' . htmlspecialchars(implode('; ', $errors), ENT_QUOTES, 'UTF-8'));
    }

    // All six tables are written inside one transaction so a partial failure
    // cannot leave a proposal with half-deleted child rows.
    $conn->begin_transaction();
    try {

    if($is_update) {
        $prop_id = intval($_GET['id']);

        // Confirm ownership BEFORE touching any child table. Previously the
        // ownership check lived only in the UPDATE's WHERE clause, so a POST
        // naming someone else's proposal left the parent row intact but still
        // deleted and rewrote that owner's financials, guests, budgets and
        // sponsors.
        $own = $conn->prepare("SELECT status FROM proposals WHERE id = ? AND user_id = ?");
        $own->bind_param("ii", $prop_id, $user_id);
        $own->execute();
        $ownRes = $own->get_result();
        if($ownRes->num_rows === 0) {
            $own->close();
            $conn->rollback();
            http_response_code(404);
            exit('Proposal not found or you are not authorised to edit it.');
        }
        $existing_status = $ownRes->fetch_assoc()['status'];
        $own->close();

        // A decided proposal must not be silently rewritten underneath the HOD.
        if(in_array($existing_status, ['Approved', 'Rejected', 'Cancelled'], true)) {
            $conn->rollback();
            http_response_code(409);
            exit("This proposal is {$existing_status} and can no longer be edited.");
        }

        $q = "UPDATE proposals SET title=?, description=?, category=?, start_date=?, end_date=?, past_events=?, other_details=?, total_expected_participants=?, participant_categories=?, student_categories=? WHERE id=? AND user_id=?";
        $stmt = $conn->prepare($q);
        $stmt->bind_param("sssssssisssi", $title, $desc, $category, $start_date, $end_date, $past_events, $other_details, $total_pax, $part_cats, $stud_cats, $prop_id, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $q = "INSERT INTO proposals (user_id, title, description, category, start_date, end_date, past_events, other_details, total_expected_participants, participant_categories, student_categories, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($q);
        $stmt->bind_param("isssssssiss", $user_id, $title, $desc, $category, $start_date, $end_date, $past_events, $other_details, $total_pax, $part_cats, $stud_cats);
        $stmt->execute();
        $prop_id = $stmt->insert_id;
        $stmt->close();
    }

    // Child rows are replaced wholesale on every save. $prop_id is now known to
    // belong to $user_id (verified above, or freshly inserted), and each delete
    // is a prepared statement.
    $clearChild = function(string $table) use ($conn, $prop_id) {
        $st = $conn->prepare("DELETE FROM `{$table}` WHERE proposal_id = ?");
        $st->bind_param("i", $prop_id);
        $st->execute();
        $st->close();
    };

    // 2. Financials
    $fu = floatval($_POST['fund_uni'] ?? 0);
    $fr = floatval($_POST['fund_reg'] ?? 0);
    $fs = floatval($_POST['fund_sponsor'] ?? 0);
    $fo = floatval($_POST['fund_other'] ?? 0);

    $clearChild('proposal_financials');
    $qFin = "INSERT INTO proposal_financials (proposal_id, university_fund, registration_fund, sponsorship_fund, other_sources) VALUES (?, ?, ?, ?, ?)";
    $stF = $conn->prepare($qFin);
    $stF->bind_param("idddd", $prop_id, $fu, $fr, $fs, $fo);
    $stF->execute();
    $stF->close();

    // 3. Chief Guest
    $clearChild('proposal_guests');
    if(!empty($_POST['cg_name'])) {
        $cgn = $_POST['cg_name'];
        $cgd = $_POST['cg_designation'] ?? null;
        $cga = $_POST['cg_address'] ?? null;
        $cgp = $_POST['cg_phone'] ?? null; $cgpan = $_POST['cg_pan'] ?? null; $cgr = $_POST['cg_reason'] ?? null;
        $qG = "INSERT INTO proposal_guests (proposal_id, name, designation, address, contact_number, pan_number, reason_for_inviting) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stG = $conn->prepare($qG);
        $stG->bind_param("issssss", $prop_id, $cgn, $cgd, $cga, $cgp, $cgpan, $cgr);
        $stG->execute(); $stG->close();
    }

    // 4. Travel & Accommodation
    $clearChild('proposal_travel_accomm');
    // Accommodation mapping
    if(!empty($_POST['hotel_name'])) {
        $hn = $_POST['hotel_name'] . ' - ' . ($_POST['hotel_address']??'');
        $hd = intval($_POST['hotel_duration']??0);
        $ht = ($_POST['hotel_type'] ?? '') === 'srm' ? 'SRM' : 'Guest';
        $qT1 = "INSERT INTO proposal_travel_accomm (proposal_id, hotel_name_address, accommodation_days, who_arranges) VALUES (?, ?, ?, ?)";
        $stT1 = $conn->prepare($qT1);
        $stT1->bind_param("isis", $prop_id, $hn, $hd, $ht);
        $stT1->execute(); $stT1->close();
    }
    // Travel mapping
    if(!empty($_POST['travel_name'])) {
        $tm = $_POST['travel_name'];
        $td = intval($_POST['travel_duration']??0);
        $tt = ($_POST['travel_type'] ?? '') === 'srm' ? 'SRM' : 'Guest';
        $taddr = $_POST['travel_address'] ?? null;
        $qT2 = "INSERT INTO proposal_travel_accomm (proposal_id, mode, number_of_trips, who_provides, travel_address) VALUES (?, ?, ?, ?, ?)";
        $stT2 = $conn->prepare($qT2);
        $stT2->bind_param("isiss", $prop_id, $tm, $td, $tt, $taddr);
        $stT2->execute(); $stT2->close();
    }

    // 5. Budgets (JSON decode)
    $clearChild('proposal_budgets');
    $bJson = json_decode($_POST['budget_json'] ?? '[]', true);
    if(is_array($bJson)) {
        $qB = "INSERT INTO proposal_budgets (proposal_id, category, type, quantity, cost_per_unit, total) VALUES (?, ?, ?, ?, ?, ?)";
        $stB = $conn->prepare($qB);
        foreach($bJson as $b) {
            if(!is_array($b)) continue;
            if(empty($b['category']) && empty($b['sub_category'])) continue;
            $cat = ($b['category'] ?? '') . ' - ' . ($b['sub_category'] ?? '');
            $qty = intval($b['quantity'] ?? 0); $cst = floatval($b['cost'] ?? 0); $tot = floatval($b['amount'] ?? 0);
            $typeStr = $b['type'] ?? null;
            $stB->bind_param("issidd", $prop_id, $cat, $typeStr, $qty, $cst, $tot);
            $stB->execute();
        }
        $stB->close();
    }

    // 6. Sponsors (JSON decode)
    $clearChild('proposal_sponsors');
    $sJson = json_decode($_POST['sponsor_json'] ?? '[]', true);
    if(is_array($sJson)) {
        $qS = "INSERT INTO proposal_sponsors (proposal_id, sponsor_category, amount_contributed, reward_perk, mode, about, benefits) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stS = $conn->prepare($qS);
        foreach($sJson as $s) {
            if(!is_array($s)) continue;
            if(empty($s['category'])) continue;
            $scat = $s['category'];
            $amt = floatval($s['amount'] ?? 0);
            $srew = $s['reward'] ?? null; $smode = $s['mode'] ?? null;
            $sabout = $s['about'] ?? null; $sben = $s['benefit'] ?? null;
            $stS->bind_param("isdssss", $prop_id, $scat, $amt, $srew, $smode, $sabout, $sben);
            $stS->execute();
        }
        $stS->close();
    }

        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        $ref = ec_log_exception($e, 'proposal_save');
        http_response_code(500);
        exit("Could not save the proposal; no changes were made. Reference: {$ref}");
    }

    header("Location: dashboard.php?alert=Proposal%20Saved");
    exit;
}

// Prepare Edit Data if requested
if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $prop_id = intval($_GET['id']);
    $query = "SELECT p.*, pf.university_fund, pf.registration_fund, pf.sponsorship_fund, pf.other_sources 
              FROM proposals p LEFT JOIN proposal_financials pf ON p.id = pf.proposal_id 
              WHERE p.id = ? AND p.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $prop_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $edit_data = $result->fetch_assoc();
        
        // Fetch Guests
        $gq = $conn->query("SELECT * FROM proposal_guests WHERE proposal_id = $prop_id LIMIT 1");
        if($gq->num_rows > 0) $edit_data['guest'] = $gq->fetch_assoc();
        
        // Fetch Travel
        $tq = $conn->query("SELECT * FROM proposal_travel_accomm WHERE proposal_id = $prop_id");
        $edit_data['travel'] = [];
        while($tr = $tq->fetch_assoc()) $edit_data['travel'][] = $tr;
        
        // Fetch Budgets
        $bq = $conn->query("SELECT * FROM proposal_budgets WHERE proposal_id = $prop_id");
        $edit_data['budgets'] = [];
        while($br = $bq->fetch_assoc()) $edit_data['budgets'][] = $br;
        
        // Fetch Sponsors
        $sq = $conn->query("SELECT * FROM proposal_sponsors WHERE proposal_id = $prop_id");
        $edit_data['sponsors'] = [];
        while($sr = $sq->fetch_assoc()) $edit_data['sponsors'][] = $sr;
    }
    $stmt->close();
}

require_once 'views/proposal.view.php';
?>
