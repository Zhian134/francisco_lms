<?php
require_once('../classes/database.php');
$con = new database();
$actionStatus = null;
$actionMessage = '';

if(isset($_POST['add_book'])){
    try{
        $con->insertbook($_POST['book_title'], $_POST['book_isbn'], $_POST['book_publication_year'], $_POST['book_edition'], $_POST['book_publisher']);
        $actionStatus = 'success';
        $actionMessage = 'Book Added Successfully';
    } catch(Exception $e){
        $actionStatus = 'error';
        $actionMessage = $e->getMessage();
    }
}

if(isset($_POST['update_book'])){
    try {
        $con->updateBook($_POST['book_id'], $_POST['book_title'], $_POST['book_isbn'], $_POST['book_publication_year'], $_POST['book_publisher']);
        $actionStatus = 'success';
        $actionMessage = 'Book Updated Successfully';
    } catch(Exception $e){
        $actionStatus = 'error';
        $actionMessage = 'Error updating Book: ' . $e->getMessage();
    }
}

if (isset($_POST['delete_book'])) {
    try {
        $con->delete_book($_POST['book_id']);
        $actionStatus = 'success';
        $actionMessage = 'Book Deleted Successfully';
    } catch (Exception $e) {
        $actionStatus = 'error';
        $actionMessage = 'Cannot delete book. It might be linked to authors, genres, or copies.';
    }
}

if(isset($_POST['add_copy'])){
    try{
        $con->insertBookCopy($_POST['book_id'], $_POST['status']);
        $actionStatus = 'success';
        $actionMessage = 'Copy Added Successfully';
    } catch(Exception $e){ $actionStatus = 'error'; $actionMessage = 'Error adding Copy'; }
}

if(isset($_POST['assign_author'])){
    try{
        $con->insertBookAuthor($_POST['book_id'], $_POST['author_id']);
        $actionStatus = 'success';
        $actionMessage = 'Author Assigned Successfully';
    } catch(Exception $e){
        $actionStatus = 'error';
        $actionMessage = 'This author is already assigned to this book.';
    }
}

if(isset($_POST['assign_genre'])){
    try{
        $con->insertBookGenre($_POST['book_id'], $_POST['genre_id']);
        $actionStatus = 'success';
        $actionMessage = 'Genre Assigned Successfully';
    } catch(Exception $e){
        $actionStatus = 'error';
        $actionMessage = 'This genre is already assigned to this book.';
    }
}

$booksList = $con->viewBooks();
$authorsList = $con->viewAuthors(); 
$genresList = $con->viewGenres();   
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Books — Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../sweetalert/dist/sweetalert2.css">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="admin-dashboard.html">Library Admin</a>
    <div id="navBooks" class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="admin-dashboard.html">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="books.php">Books</a></li>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-4">
  <div class="row g-3">
    <div class="col-12 col-lg-4">
      <div class="card p-4 mb-3">
        <h5>Add Book</h5>
        <form method="POST">
          <div class="mb-2"><label class="form-label">Title</label><input class="form-control" name="book_title" required></div>
          <div class="mb-2"><label class="form-label">ISBN</label><input class="form-control" name="book_isbn"></div>
          <div class="mb-2"><label class="form-label">Year</label><input class="form-control" name="book_publication_year" type="number"></div>
          <div class="mb-2"><label class="form-label">Edition</label><input class="form-control" name="book_edition"></div>
          <div class="mb-2"><label class="form-label">Publisher</label><input class="form-control" name="book_publisher"></div>
          <button name="add_book" class="btn btn-primary w-100 mt-2" type="submit">Add Book</button>
        </form>
      </div>

      <div class="card p-4">
        <h6>Add Copy</h6>
        <form method="POST">
          <div class="mb-2">
            <select class="form-select" name="book_id" required>
              <option value="">Select book</option>
              <?php foreach($booksList as $b): ?><option value="<?= $b['book_id'] ?>"><?= htmlspecialchars($b['book_title']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <select class="form-select" name="status" required>
              <option value="AVAILABLE">AVAILABLE</option>
              <option value="ON_LOAN">ON_LOAN</option>
              <option value="LOST">LOST</option>
              <option value="DAMAGED">DAMAGED</option>
              <option value="REPAIR">REPAIR</option>
            </select>
          </div>
          <button name="add_copy" class="btn btn-outline-primary w-100" type="submit">Add Copy</button>
        </form>
      </div>
    </div>

    <div class="col-12 col-lg-8">
      <div class="card p-4">
        <h5 class="mb-3">Books List</h5>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead class="table-light">
              <tr>
                <th>ID</th><th>Title</th><th>ISBN</th><th>Year</th><th>Publisher</th><th>Copies</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($booksList as $book): ?>
                <tr>
                  <td><?= $book['book_id'] ?></td>
                  <td><?= htmlspecialchars($book['book_title']) ?></td>
                  <td><?= htmlspecialchars($book['book_isbn']) ?></td>
                  <td><?= $book['book_publication_year'] ?></td>
                  <td><?= htmlspecialchars($book['book_publisher']) ?></td>
                  <td class="text-center"><span class="badge text-bg-success"><?= $book['available_copies'] ?? 0 ?></span></td>
                  <td class="text-end">
                    <div class="btn-group">
                      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editBookModal" 
                        data-book-id="<?= $book['book_id'] ?>" 
                        data-book-title="<?= htmlspecialchars($book['book_title'], ENT_QUOTES) ?>"
                        data-book-isbn="<?= htmlspecialchars($book['book_isbn'], ENT_QUOTES) ?>"
                        data-book-year="<?= $book['book_publication_year'] ?>"
                        data-book-publisher="<?= htmlspecialchars($book['book_publisher'], ENT_QUOTES) ?>"
                      >Edit</button>
                      <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="<?= $book['book_id'] ?>">Delete</button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <hr class="my-4">

        <div class="row g-3">
          <div class="col-md-6">
            <div class="border rounded p-3">
              <h6 class="mb-2">Assign Author</h6>
              <form method="POST" class="row g-2">
                <div class="col-12">
                  <select class="form-select" name="book_id" required>
                    <option value="">Select book</option>
                    <?php foreach($booksList as $b): ?><option value="<?= $b['book_id'] ?>"><?= htmlspecialchars($b['book_title']) ?></option><?php endforeach; ?>
                  </select>
                </div>
                <div class="col-12">
                  <select class="form-select" name="author_id" required>
                    <option value="">Select author</option>
                    <?php foreach($authorsList as $a): ?><option value="<?= $a['author_id'] ?? $a['id'] ?>"><?= htmlspecialchars($a['author_name'] ?? 'Author #'.$a['author_id']) ?></option><?php endforeach; ?>
                  </select>
                </div>
                <button name="assign_author" class="btn btn-outline-primary btn-sm mx-2" type="submit">Assign</button>
              </form>
            </div>
          </div>
          <div class="col-md-6">
            <div class="border rounded p-3">
              <h6 class="mb-2">Assign Genre</h6>
              <form method="POST" class="row g-2">
                <div class="col-12">
                  <select class="form-select" name="book_id" required>
                    <option value="">Select book</option>
                    <?php foreach($booksList as $b): ?><option value="<?= $b['book_id'] ?>"><?= htmlspecialchars($b['book_title']) ?></option><?php endforeach; ?>
                  </select>
                </div>
                <div class="col-12">
                  <select class="form-select" name="genre_id" required>
                    <option value="">Select genre</option>
                    <?php foreach($genresList as $g): ?><option value="<?= $g['genre_id'] ?? $g['id'] ?>"><?= htmlspecialchars($g['genre_name'] ?? 'Genre #'.$g['genre_id']) ?></option><?php endforeach; ?>
                  </select>
                </div>
                <button name="assign_genre" class="btn btn-outline-primary btn-sm mx-2" type="submit">Assign</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<div class="modal fade" id="editBookModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Edit Book</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" name="book_id" id="edit_book_id">
        <div class="mb-3"><label class="form-label">Title</label><input type="text" name="book_title" id="edit_book_title" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">ISBN</label><input type="text" name="book_isbn" id="edit_book_isbn" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Year</label><input type="number" name="book_publication_year" id="edit_book_year" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Publisher</label><input type="text" name="book_publisher" id="edit_book_publisher" class="form-control"></div>
      </div>
      <div class="modal-footer"><button type="submit" name="update_book" class="btn btn-success">Save Changes</button></div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../sweetalert/dist/sweetalert2.js"></script>

<script>
  // Clean initialization of variables
  const actionStatus = <?= json_encode($actionStatus) ?>;
  const actionMessage = <?= json_encode($actionMessage) ?>;

  if (actionStatus === 'success') {
      Swal.fire('Success', actionMessage, 'success');
  } else if (actionStatus === 'error') {
      Swal.fire('Error', actionMessage, 'error');
  }

  // Edit Modal logic
  const editModal = document.getElementById('editBookModal');
  if (editModal) {
    editModal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        document.getElementById('edit_book_id').value = btn.getAttribute('data-book-id');
        document.getElementById('edit_book_title').value = btn.getAttribute('data-book-title');
        document.getElementById('edit_book_isbn').value = btn.getAttribute('data-book-isbn');
        document.getElementById('edit_book_year').value = btn.getAttribute('data-book-year');
        document.getElementById('edit_book_publisher').value = btn.getAttribute('data-book-publisher');
    });
  }

  // Delete logic
  document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function() {
        const bookId = this.getAttribute('data-id');
        Swal.fire({
            title: 'Delete this book?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                // Fixed: Added backticks for template literals
                form.innerHTML = `<input type="hidden" name="book_id" value="${bookId}"><input type="hidden" name="delete_book" value="1">`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
  });
</script>
</body>
</html>