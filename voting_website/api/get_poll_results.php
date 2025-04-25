<?php
// api/get_poll_results.php
require 'db_connect.php';
require 'helpers.php'; // For checkLogin potentially, though results might be public

// Decide if results require login - for now, let's assume yes.
// If results should be public, remove or comment out checkLogin().
checkLogin();

header('Content-Type: application/json');
$response = ['success' => false, 'results' => null, 'message' => '', 'poll_question' => ''];
$userId = $_SESSION['user_id'] ?? 0; // Get user ID to check if they voted

if (!isset($_GET['poll_id'])) {
    $response['message'] = 'Poll ID is required.';
    echo json_encode($response);
    exit;
}

$pollId = filter_input(INPUT_GET, 'poll_id', FILTER_VALIDATE_INT);

if ($pollId === false || $pollId <= 0) {
    $response['message'] = 'Invalid Poll ID.';
    echo json_encode($response);
    exit;
}

try {
    // 1. Get Poll Question and check if current user voted
    $sqlPollInfo = "SELECT question,
                      (SELECT COUNT(*) FROM votes v WHERE v.poll_id = p.id AND v.user_id = :user_id) > 0 AS has_voted
                    FROM polls p
                    WHERE p.id = :poll_id";
    $stmtPollInfo = $pdo->prepare($sqlPollInfo);
    $stmtPollInfo->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmtPollInfo->bindParam(':poll_id', $pollId, PDO::PARAM_INT);
    $stmtPollInfo->execute();
    $pollInfo = $stmtPollInfo->fetch();

    if (!$pollInfo) {
        $response['message'] = 'Poll not found.';
        echo json_encode($response);
        exit;
    }

    $response['poll_question'] = $pollInfo['question'];
    $response['user_has_voted'] = (bool)$pollInfo['has_voted'];

    // 2. Get options and vote counts
    // Use a LEFT JOIN to include options with zero votes
    $sqlResults = "SELECT
                       po.id AS option_id,
                       po.option_text,
                       COUNT(v.id) AS vote_count
                   FROM poll_options po
                   LEFT JOIN votes v ON po.id = v.option_id AND v.poll_id = po.poll_id
                   WHERE po.poll_id = :poll_id
                   GROUP BY po.id, po.option_text
                   ORDER BY po.id"; // Or ORDER BY vote_count DESC

    $stmtResults = $pdo->prepare($sqlResults);
    $stmtResults->bindParam(':poll_id', $pollId, PDO::PARAM_INT);
    $stmtResults->execute();
    $resultsData = $stmtResults->fetchAll();

    // 3. Calculate total votes
    $totalVotes = 0;
    foreach ($resultsData as $row) {
        $totalVotes += (int)$row['vote_count'];
    }

    // 4. Format results, calculate percentage
    $formattedResults = [];
    foreach ($resultsData as $row) {
        $percentage = ($totalVotes > 0) ? round(((int)$row['vote_count'] / $totalVotes) * 100, 1) : 0;
        $formattedResults[] = [
            'option_id' => $row['option_id'],
            'text' => $row['option_text'],
            'count' => (int)$row['vote_count'],
            'percentage' => $percentage
        ];
    }

    $response['success'] = true;
    $response['results'] = $formattedResults;
    $response['total_votes'] = $totalVotes;


} catch (PDOException $e) {
    error_log("Get Poll Results Error: " . $e->getMessage());
    $response['message'] = 'An error occurred while fetching poll results.';
    // In production, don't expose $e->getMessage()
}

echo json_encode($response);
?>