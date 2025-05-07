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

  if ($date_loan > $date_return) {
      echo "<p style='color:red'>La fecha de devolución no puede ser anterior a la de préstamo.</p>";
  } else {
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM Loan l JOIN BookLoan bl ON l.loan_id = bl.loan_id WHERE bl.book_id = ? AND l.status = 'active'");
  if ($stmt->execute([$book_id]) && $stmt->fetchColumn() > 0) {
      echo "<p style='color:red'>El libro ya está prestado.</p>";
  } else {
      $stmt = $pdo->prepare("INSERT INTO Loan (user_id, date_loan, date_return, status) VALUES (?, ?, ?, 'active')");
      if ($stmt->execute([$user_id, $date_loan, $date_return])) {
          $loan_id = $pdo->lastInsertId();
          $stmt = $pdo->prepare("INSERT INTO BookLoan (loan_id, book_id) VALUES (?, ?)");
          if ($stmt->execute([$loan_id, $book_id])) {
              echo "<p style='color:green'>Préstamo registrado correctamente.</p>";
          } else {
              echo "<p style='color:red'>Error al registrar el préstamo.</p>";
          }
      } else {
          echo "<p style='color:red'>Error al registrar el préstamo.</p>";
      }
  }
  }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
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
       <div class="loan-status">Actualmente en préstamo</div>
    <?php else: ?>
        <button type="button" class="loan-btn" onclick="openLoanModal()">Hacer préstamo</button>
    <?php endif; ?>
      <div id="loanModal" class="modal" style="display:none;">
        <div class="modal-content">
          <span class="close" onclick="closeLoanModal()">&times;</span>
          <h2>Formulario de préstamo</h2>
          <form id="loanForm" method="post" action="?id=<?= $book['book_id'] ?>">
            <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>">
            <div class="form-group">
              <label for="user_id">Usuario:</label>
              <select name="user_id" id="user_id" required>
                <?php
                $users = $pdo->query("SELECT user_id, name FROM Users")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($users as $user):
                ?>
                  <option value="<?= $user['user_id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="date_loan">Fecha de préstamo:</label>
              <input type="date" name="date_loan" id="date_loan" required>
            </div>
            <div class="form-group">
              <label for="date_return">Fecha de devolución:</label>
              <input type="date" name="date_return" id="date_return" required>
            </div>
            <button type="submit" id="confirmLoanBtn">Confirmar préstamo</button>
            <button type="button" onclick="closeLoanModal()">Cancelar</button>
            <div id="loanResult" style="margin-top:10px;"></div>
          </form>
        </div>
      </div>
      <script>
        function openLoanModal() {
          document.getElementById('loanModal').style.display = 'block';
        }
        function closeLoanModal() {
          document.getElementById('loanModal').style.display = 'none';
          document.getElementById('loanResult').innerHTML = '';
        }
      </script>
      <style>
        .modal { position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background: rgba(0,0,0,0.3); }
        .modal-content { background: #fff; margin: 10% auto; padding: 24px 32px; border-radius: 10px; width: 90%; max-width: 400px; box-shadow: 0 4px 24px #0002; position: relative; text-align: center; }
        .close { position: absolute; right: 18px; top: 10px; font-size: 1.7em; cursor: pointer; color: #888; }
        .close:hover { color: #e74c3c; }
        #confirmLoanBtn { background: #4f8cff; color: #fff; border: none; padding: 10px 22px; border-radius: 6px; margin-right: 10px; cursor: pointer; font-size: 1em; }
        #confirmLoanBtn:hover { background: #2d3a4b; }
        #loanModal button { background: #eee; color: #2d3a4b; border: none; padding: 10px 22px; border-radius: 6px; cursor: pointer; font-size: 1em; }
        #loanModal button:hover { background: #ddd; }
      </style>
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