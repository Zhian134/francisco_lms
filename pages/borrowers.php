<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../classes/database.php');
$con = new database();

$borrowerCreateStatus = null;
$borrowerCreateMessage = '';

$borrowerAddressStatus = null;
$borrowerAddressMessage = '';

// --- Logic for Creating Borrower Account ---
if (isset($_POST['add_borrower'])) {
    $firstname = $_POST['borrower_firstname'];
    $lastname = $_POST['borrower_lastname'];
    $email = $_POST['borrower_email'];
    $phone = $_POST['borrower_phone_number'];
    $member_since = $_POST['borrower_member_since'];
    $is_active = $_POST['is_active'];
    $temp_password = $_POST['temp_password'];

    $user_password_hash = password_hash($temp_password, PASSWORD_DEFAULT);

    try {
        // Start transaction - Requires beginTransaction() in database.php
        $con->beginTransaction();
        
        $user_id = $con->insertUser($email, $user_password_hash, $is_active);
        $borrower_id = $con->insertBorrower($firstname, $lastname, $email, $phone, $member_since, $is_active);
        $con->insertBorrowerUser($user_id, $borrower_id);
        
        $con->commit();

        $borrowerCreateStatus = 'success';
        $borrowerCreateMessage = 'Borrower account created successfully';
    } catch(Exception $e) {
        $con->rollBack();
        $borrowerCreateStatus = 'error';
        $borrowerCreateMessage = 'Error: ' . $e->getMessage();
    }
}

// --- Logic for Adding Address ---
if (isset($_POST['add'])) {
    $borrow_id = $_POST['borrower_id'];
    $house_number = $_POST['ba_house_number'];
    $street = $_POST['ba_street'];
    $barangay = $_POST['ba_barangay'];
    $city = $_POST['ba_city'];
    $province = $_POST['ba_province'];
    $postalcode = $_POST['ba_postal_code'];
    $primary = $_POST['is_primary'];

    try {
        $result = $con->insertborroweraddress($borrow_id, $house_number, $street, $barangay, $city, $province, $postalcode, $primary);
        if($result) {
            $borrowerAddressStatus = 'success';
            $borrowerAddressMessage = 'Address added successfully';
        } else {
            throw new Exception("Could not save address to database.");
        }
    } catch(Exception $e) {
        $borrowerAddressStatus = 'error';
        $borrowerAddressMessage = 'Failed: ' . $e->getMessage();
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Borrowers Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../sweetalert/dist/sweetalert2.css">
  <style>
    .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
    .form-label { font-size: 0.875rem; font-weight: 500; }
  </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">Library Admin</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="admin-dashboard.html">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active text-primary" href="borrowers.php">Borrowers</a></li>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-4">
  <div class="row g-4">
    <div class="col-12">
      <div class="card p-4">
        <h5 class="mb-3">Active Borrowers</h5>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email Address</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $list = $con->viewborrowers();
              foreach($list as $row): ?>
              <tr>
                <td><strong>#<?php echo $row['borrower_id']; ?></strong></td>
                <td><?php echo htmlspecialchars($row['borrower_firstname'] . ' ' . $row['borrower_lastname']); ?></td>
                <td><?php echo htmlspecialchars($row['borrower_email']); ?></td>
                <td>
                    <span class="badge <?php echo $row['is_active'] ? 'bg-success-subtle text-success border border-success' : 'bg-secondary-subtle text-secondary border border-secondary'; ?>">
                    <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                    </span>
                </td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-primary px-3" data-bs-toggle="modal" data-bs-target="#resetPassModal">Reset Pass</button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card p-4 h-100">
        <h5 class="mb-3">Register New Borrower</h5>
        <form action="" method="POST" class="row g-3">
          <div class="col-md-6">
            <label class="form-label">First Name</label>
            <input class="form-control" name="borrower_firstname" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Last Name</label>
            <input class="form-control" name="borrower_lastname" required>
          </div>
          <div class="col-12">
            <label class="form-label">Email (Username)</label>
            <input class="form-control" name="borrower_email" type="email" required>
          </div>
          <div class="col-12">
            <label class="form-label">Phone Number</label>
            <input class="form-control" name="borrower_phone_number" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Date Joined</label>
            <input class="form-control" name="borrower_member_since" type="date" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Account Active</label>
            <select class="form-select" name="is_active">
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Temporary Password</label>
            <input class="form-control" name="temp_password" type="password" required>
          </div>
          <div class="col-12">
            <button name="add_borrower" class="btn btn-primary w-100 py-2" type="submit">Create Account</button>
          </div>
        </form>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card p-4 h-100">
        <h5 class="mb-3">Add Contact Address</h5>
        <form action="" method="POST" class="row g-3">
          <div class="col-12">
            <label class="form-label">Select Borrower</label>
            <select class="form-select" name="borrower_id" required>
              <option value="">Choose...</option>
              <?php foreach($list as $b): ?>
                <option value="<?php echo $b['borrower_id']; ?>"><?php echo htmlspecialchars($b['borrower_firstname'] . ' ' . $b['borrower_lastname']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label">House/Bldg #</label>
            <input class="form-control" name="ba_house_number">
          </div>
          <div class="col-6">
            <label class="form-label">Street</label>
            <input class="form-control" name="ba_street">
          </div>
          <div class="col-12">
            <label class="form-label">Barangay</label>
            <input class="form-control" name="ba_barangay">
          </div>
          <div class="col-md-6">
            <label class="form-label">City</label>
            <input class="form-control" name="ba_city">
          </div>
          <div class="col-md-6">
            <label class="form-label">Province</label>
            <input class="form-control" name="ba_province">
          </div>
          <div class="col-md-6">
            <label class="form-label">Postal Code</label>
            <input class="form-control" name="ba_postal_code">
          </div>
          <div class="col-md-6">
            <label class="form-label">Set as Primary?</label>
            <select class="form-select" name="is_primary">
              <option value="1">Yes</option>
              <option value="0" selected>No</option>
            </select>
          </div>
          <div class="col-12">
            <button class="btn btn-outline-primary w-100 py-2" type="submit" name="add">Save Address</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../sweetalert/dist/sweetalert2.all.min.js"></script>

<script>
  const createStatus = <?php echo json_encode($borrowerCreateStatus) ?>;
  const createMsg = <?php echo json_encode($borrowerCreateMessage) ?>;
  const addrStatus = <?php echo json_encode($borrowerAddressStatus) ?>;
  const addrMsg = <?php echo json_encode($borrowerAddressMessage) ?>;

  if(createStatus) {
    Swal.fire({ 
        icon: createStatus, 
        title: createStatus === 'success' ? 'Created!' : 'Error', 
        text: createMsg,
        confirmButtonColor: '#0d6efd'
    });
  }
  if(addrStatus) {
    Swal.fire({ 
        icon: addrStatus, 
        title: addrStatus === 'success' ? 'Saved!' : 'Failed', 
        text: addrMsg,
        confirmButtonColor: '#0d6efd'
    });
  }
</script>
</body>
</html>