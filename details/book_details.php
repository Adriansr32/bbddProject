<?php
$host = 'localhost';
$db = 'digital_library';
$user = 'gestor';
$pass = 'gestor123';

require_once 'query.php'; 

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $book_id = (int)$_GET['id'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $book = obtenerDetallesLibro($pdo, $book_id);
        $ratings = obtenerValoraciones($pdo, $book_id);

    } catch (PDOException $e) {
        die('Connection error: ' . $e->getMessage());
    }
} else {
    die('Invalid book ID.');
}

    
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
  $book_id = (int)$_GET['id'];
  $user_id = $_POST['user_id'];
  $date_loan = $_POST['date_loan'];
  $date_return = $_POST['date_return'];

  $notification = null;
  $notification_type = null;

  if ($date_loan > $date_return) {
      $notification = "Return date cannot be earlier than loan date.";
      $notification_type = "error";
  } else {
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM Loan l JOIN BookLoan bl ON l.loan_id = bl.loan_id WHERE bl.book_id = ? AND l.status = 'active'");
      if ($stmt->execute([$book_id]) && $stmt->fetchColumn() > 0) {
          $notification = "This book is already on loan.";
          $notification_type = "error";
      } else {
          $stmt = $pdo->prepare("INSERT INTO Loan (user_id, date_loan, date_return, status) VALUES (?, ?, ?, 'active')");
          if ($stmt->execute([$user_id, $date_loan, $date_return])) {
              $loan_id = $pdo->lastInsertId();
              $stmt = $pdo->prepare("INSERT INTO BookLoan (loan_id, book_id) VALUES (?, ?)");
              if ($stmt->execute([$loan_id, $book_id])) {
                  $notification = "Loan registered successfully.";
                  $notification_type = "success";
              } else {
                  $notification = "Error registering the loan.";
                  $notification_type = "error";
              }
          } else {
              $notification = "Error registering the loan.";
              $notification_type = "error";
          }
      }
  }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <?php if (!empty($book['cover'])): ?>
  <link rel="icon" type="image/jpeg" href="../img/DL.png">
<?php endif; ?>
  <title>Book: <?= htmlspecialchars($book['title']) ?></title>
  <link rel="stylesheet" href="details.css">
</head>
<body>
  <h1>Book Details</h1>

  <div class="book-details">
    <img src="data:image/jpeg;base64,<?= base64_encode($book['cover']) ?>" alt="Cover of <?= htmlspecialchars($book['title']) ?>">
    <h2><?= htmlspecialchars($book['title']) ?></h2>
    <div class="details-info">
      <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
      <p><strong>ISBN:</strong> <?= htmlspecialchars($book['isbn']) ?></p>
      <p><strong>Publisher:</strong> <?= htmlspecialchars($book['editorial']) ?></p>
      <p><strong>Year of publication:</strong> <?= date('Y', strtotime($book['year_publication'])) ?></p>
      <p><strong>Average rating:</strong> <?= $book['average_score'] ?? 'N/A' ?> (<?= $book['total_valorations'] ?> rating/s)</p>
    </div>
    <?php if ($book['loan_status']): ?>
       <div class="loan-status"><i class="fa fa-times-circle" style="color:#dc3545;margin-right:6px;"></i>Currently on loan</div>
    <?php else: ?>
        <button type="button" class="loan-btn" onclick="openLoanModal()">Make a loan</button>
    <?php endif; ?>
      <div id="loanModal" class="modal" style="display:none;">
        <div class="modal-content">
          <span class="close" onclick="closeLoanModal()">&times;</span>
          <div style="margin-bottom:18px;">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" style="margin-bottom:8px;"><circle cx="24" cy="24" r="24" fill="#B100FF22"/><path d="M16 24h16M24 16v16" stroke="#B100FF" stroke-width="3" stroke-linecap="round"/></svg>
            <h2 style="margin:0;font-size:1.6em;color:#4f2d7f;font-weight:700;letter-spacing:0.5px;">Request Loan</h2>
            <p style="color:#555;font-size:1.08em;margin-top:6px;">Fill out the form to request a loan for this book.</p>
          </div>
          <form id="loanForm" method="post" action="?id=<?= $book['book_id'] ?>">
            <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>">
            <div class="form-group" style="margin-bottom:14px;">
              <label for="user_id" style="font-weight:600;color:#4f2d7f;">User:</label>
              <select name="user_id" id="user_id" required>
                <?php
                $users = $pdo->query("SELECT user_id, name FROM Users")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($users as $user):
                ?>
                  <option value="<?= $user['user_id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" style="margin-bottom:14px;">
              <label for="date_loan" style="font-weight:600;color:#4f2d7f;">Loan date:</label>
              <input type="date" name="date_loan" id="date_loan" required>
            </div>
            <div class="form-group" style="margin-bottom:18px;">
              <label for="date_return" style="font-weight:600;color:#4f2d7f;">Return date:</label>
              <input type="date" name="date_return" id="date_return" required>
            </div>
            <button type="submit" id="confirmLoanBtn">Confirm loan</button>
            <button type="button" onclick="closeLoanModal()">Cancel</button>
            <div id="loanResult" style="margin-top:10px;"></div>
          </form>
        </div>
      </div>
      <script>
        var notification = <?php echo isset($notification) ? '"' . addslashes($notification) . '"' : 'null'; ?>;
        var notificationType = <?php echo isset($notification_type) ? '"' . $notification_type . '"' : 'null'; ?>;
      </script>
      <div id="notification-container" style="position:fixed;top:30px;right:30px;z-index:9999;display:none;"></div>
      <script src="script.js"></script>
    <button class="back-btn" onclick="window.location.href='../index.php'">Back to list</button>
  </div>

  <div class="ratings">
    <h3>User Ratings</h3>
    <?php foreach ($ratings as $rating): ?>
      <div class="rating">
        <p><strong><?= htmlspecialchars($rating['user_name']) ?>:</strong></p>
        <p class="score">Score: <?= htmlspecialchars($rating['score']) ?>/5</p>
        <p class="comment"><?= htmlspecialchars($rating['comments']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
