<?php
require '../../model/includes/config.php';

include_once '../common/log_header.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Help Desk Feedback</title>
    <link rel="stylesheet" href="../../../assets/CSS/feedback.css">
</head>
<body>

    <div class="breadcrumb-container">
        <nav class="breadcrumb">
            <a href="../pages/home.php" class="breadcrumb-logo">
                <img src="../../../assets/Images/logo.png" alt="Help Desk Logo" class="logo">
            </a>
            <a href="../pages/home.php" class="breadcrumb-link">Help Center</a>
            <span class="breadcrumb-separator">></span>
            <a href="#" class="breadcrumb-link active">Feedback</a>
        </nav>
    </div>

        <div class="container1">
            <h1>IT Help Desk Feedback</h1>
        </div>

    <section class="feedback-section">
        <form action="../../controller/submit_feedback.php" method="POST" class="feedback-form">
            <h2>Rate Our Service</h2>

            <!-- Rating Sections -->
            <div class="rating-section">
                <p>Rate the following aspects of our service:</p>
                <table class="rating-table">
                    <tr>
                        <th>Aspect</th>
                        <th>Poor</th>
                        <th>Fair</th>
                        <th>Good</th>
                        <th>Very Good</th>
                        <th>Excellent</th>
                    </tr>
                    <tr>
                        <td>Response Time</td>
                        <td><input type="radio" name="response_time" value="1"></td>
                        <td><input type="radio" name="response_time" value="2"></td>
                        <td><input type="radio" name="response_time" value="3"></td>
                        <td><input type="radio" name="response_time" value="4"></td>
                        <td><input type="radio" name="response_time" value="5"></td>
                    </tr>
                    <tr>
                        <td>Resolution Quality</td>
                        <td><input type="radio" name="resolution_quality" value="1"></td>
                        <td><input type="radio" name="resolution_quality" value="2"></td>
                        <td><input type="radio" name="resolution_quality" value="3"></td>
                        <td><input type="radio" name="resolution_quality" value="4"></td>
                        <td><input type="radio" name="resolution_quality" value="5"></td>
                    </tr>
                    <tr>
                        <td>Communication</td>
                        <td><input type="radio" name="communication" value="1"></td>
                        <td><input type="radio" name="communication" value="2"></td>
                        <td><input type="radio" name="communication" value="3"></td>
                        <td><input type="radio" name="communication" value="4"></td>
                        <td><input type="radio" name="communication" value="5"></td>
                    </tr>
                </table>
            </div>

            <!-- Overall Rating -->
            <div class="overall-rating">
                <p>Overall, how would you rate our service?</p>
                <div class="stars">
                    <input type="radio" id="star5" name="overall_rating" value="5"><label for="star5" title="5 stars">&#9733;</label>
                    <input type="radio" id="star4" name="overall_rating" value="4"><label for="star4" title="4 stars">&#9733;</label>
                    <input type="radio" id="star3" name="overall_rating" value="3"><label for="star3" title="3 stars">&#9733;</label>
                    <input type="radio" id="star2" name="overall_rating" value="2"><label for="star2" title="2 stars">&#9733;</label>
                    <input type="radio" id="star1" name="overall_rating" value="1"><label for="star1" title="1 star">&#9733;</label>
                </div>
            </div>

            <!-- Feedback -->
            <div class="feedback-comment">
                <label for="comments">Additional Comments</label>
                <textarea id="comments" name="comments" rows="4" placeholder="Let us know how we can improve!"></textarea>
            </div>

            <button type="submit" class="btn-submit">Submit Feedback</button>
        </form>
    </section>

    <!-- Display Feedback Section -->
    <?php include '../../model/retrieve_feedback.php'; ?>

    <section class="view-feedback">
    <h2>Feedback from Others</h2>
    <?php if (count($feedbacks) > 0): ?>
        <div class="feedback-list">
            <?php foreach ($feedbacks as $feedback): ?>
                <div class="feedback-item">
                    <p><strong>Response Time:</strong> <?php echo htmlspecialchars($feedback['response_time']); ?>/5</p>
                    <p><strong>Resolution Quality:</strong> <?php echo htmlspecialchars($feedback['resolution_quality']); ?>/5</p>
                    <p><strong>Communication:</strong> <?php echo htmlspecialchars($feedback['communication']); ?>/5</p>
                    <p><strong>Overall Rating:</strong> <?php echo htmlspecialchars($feedback['overall_rating']); ?>/5</p>
                    <p><strong>Comments:</strong> <?php echo htmlspecialchars($feedback['comments']); ?></p>
                    <p><small><em>Submitted on: <?php echo htmlspecialchars($feedback['created_at']); ?></em></small></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No feedback available yet.</p>
    <?php endif; ?>
</section>

<div class="card">
  <div class="container">
    <div class="cloud front">
      <span class="left-front"></span>
      <span class="right-front"></span>
    </div>
    <span class="sun sunshine"></span>
    <span class="sun"></span>
    <div class="cloud back">
      <span class="left-back"></span>
      <span class="right-back"></span>
    </div>
  </div>



    <?php
             include_once '../common/footer.php';

    ?>
</body>
</html>
