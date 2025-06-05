<?php
// api/get_polls.php
require 'db_connect.php';
require 'helpers.php';

checkLogin(); // Ensure user is logged in to view polls

header('Content-Type: application/json');
$response = ['success' => false, 'polls' => [], 'message' => ''];
$userId = $_SESSION['user_id'];

try {
    // Fetch polls and their options
    // We also check if the current user has already voted in each poll
    $sql = "SELECT
                p.id AS poll_id,
                p.question,
                p.closes_at, -- Fetch the closing time
                u.username AS created_by,
                p.created_at,
                GROUP_CONCAT(po.id, '||') AS option_ids,
                GROUP_CONCAT(po.option_text, '||') AS option_texts,
                (SELECT COUNT(*) FROM votes v WHERE v.poll_id = p.id AND v.user_id = ?) > 0 AS has_voted,
                -- Determine if poll is open (NULL closes_at OR closes_at > NOW())
                (p.closes_at IS NULL OR p.closes_at > datetime('now', 'localtime')) AS is_open
            FROM polls p
            JOIN users u ON p.created_by_user_id = u.id
            LEFT JOIN poll_options po ON p.id = po.poll_id
            GROUP BY p.id, p.question, p.closes_at, u.username, p.created_at
            ORDER BY p.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $pollsData = $stmt->fetchAll();

    $polls = [];
    foreach ($pollsData as $poll) {
        // *** ADD DETAILED LOGGING FOR EACH POLL ***
        error_log("Poll ID: " . $poll['poll_id'] .
                  ", closes_at from DB: " . $poll['closes_at'] .
                  ", calculated is_open: " . ($poll['is_open'] ? 'true' : 'false'));
        // *** END DETAILED LOGGING ***
        $options = [];
        if ($poll['option_ids']) { // Check if option_ids is not null or empty
            $ids = explode('||', $poll['option_ids']);
            $texts = explode('||', $poll['option_texts']);
            // Ensure counts match to prevent errors if one GROUP_CONCAT returns NULL
            if (count($ids) == count($texts)) {
                 for ($i = 0; $i < count($ids); $i++) {
                    // Check if $ids[$i] and $texts[$i] are not empty,
                    // as GROUP_CONCAT might return a single empty string
                    // if there are no matching options for a poll in a LEFT JOIN scenario.
                    if (!empty($ids[$i])) { // Basic check, you might need more robust
                         $options[] = [
                            'id' => $ids[$i],
                            'text' => $texts[$i]
                        ];
                    }
                }
            } else {
                error_log("Warning: Mismatch between option_ids and option_texts for poll_id: " . $poll['poll_id']);
            }
        }
        $polls[] = [
            'id' => $poll['poll_id'],
            'question' => $poll['question'],
            'created_by' => $poll['created_by'],
            'created_at' => date("M j, Y, g:i a", strtotime($poll['created_at'])),
            'closes_at_raw' => $poll['closes_at'], // Send raw value if needed
            'closes_at_formatted' => $poll['closes_at'] ? date("M j, Y, g:i a", strtotime($poll['closes_at'])) : null, // Formatted display
            'options' => $options,
            'has_voted' => (bool)$poll['has_voted'],
            'is_open' => (bool)$poll['is_open'] // Add the open status flag
        ];
    }

    $response['success'] = true;
    $response['polls'] = $polls;

} catch (PDOException $e) {
    error_log("Get Polls Error: " . $e->getMessage());
    $response['message'] = 'An error occurred while fetching polls.';
}

echo json_encode($response);
?>