<?php

class database{

    function opencon(): PDO{
        return new PDO("mysql:host=localhost; dbname=francisco_lms", username: "root", password: "");

    }
    function insertUser($email, $password_hash, $is_active, $member_since){
        $con = $this->opencon();
        try{
            $con->beginTransaction();
            $stmt = $con->prepare("INSERT INTO Users(username, user_password_hash, is_active) VALUES(?,?,?) ");
            $stmt->execute([$email, $password_hash, $is_active ]);
            $user_id = $con->lastInsertId();
            $con->commit();
            return $user_id;

        }catch(PDOException $e){
            if($con->inTransaction()){
                $con->rollBack();
            }
            throw $e;
        }
        

    }

  

    function insertBorrower($firstname, $lastname, $email, $phone_number, $member_since, $is_active){
        $con = $this->opencon();
        try{
            $con->beginTransaction();
            $stmt = $con->prepare("INSERT INTO borrowers(borrower_firstname, borrower_lastname, borrower_email, borrower_phone_number, borrower_member_since, is_active) VALUES(?,?,?,?,?,?) ");
            $stmt->execute([$firstname, $lastname, $email, $phone_number, $member_since, $is_active ]);
            $borrower_id = $con->lastInsertId();
            $con->commit();
            return $borrower_id;

        } catch(PDOException $e){
            if($con->inTransaction()){
                $con->rollBack();
            }
            throw $e;
        }
    }
    function insertBorrowerUser($user_id, $borrower_id){
        $con = $this->opencon();
   try{
       $con->beginTransaction();
        $stmt = $con->prepare("INSERT INTO borroweruser(user_id, borrower_id) VALUES(?,?)");
        $stmt->execute([$user_id, $borrower_id ]);
       $borroweruser_id = $con->lastInsertId();
       $con->commit();
       return $borroweruser_id;
       } 
catch(PDOException $e){
       if($con->inTransaction()){
           $con->rollBack();
       }
       throw $e;
                   }
               }



        function insertBorrowerAddress($borrower_id,$ba_house_number,$ba_street,$ba_barangay,$ba_city,$ba_province,$ba_postal_code,$is_primary){
             $con = $this->opencon();
        try{
            $con->beginTransaction();
             $stmt = $con->prepare(query: "INSERT INTO borroweraddress(borrower_id,ba_house_number,ba_street,ba_barangay,ba_city,ba_province, ba_postal_code,is_primary) VALUES(?,?,?,?,?,?,?,?)");
             $stmt->execute(params: [$borrower_id,$ba_house_number,$ba_street,$ba_barangay,$ba_city,$ba_province,$ba_postal_code,$is_primary ]);
            $ba_id = $con->lastInsertId();
            $con->commit();
            return true;
            } 
catch(PDOException $e){ 
            if($con->inTransaction()){
                $con->rollBack();
            }
            throw $e;
                        }
                    }


                    function insertbook($book_title,$book_isbn,$book_publication_year,$book_edition,$book_publisher){
                        $con = $this->opencon();
                   try{
                       $con->beginTransaction();
                        $stmt = $con->prepare("INSERT INTO books(book_title,book_isbn,book_publication_year,book_edition,book_publisher) VALUES(?,?,?,?,? )");
                        $stmt->execute( [$book_title,$book_isbn,$book_publication_year,$book_edition,$book_publisher]);
                       $book_id = $con->lastInsertId();
                       $con->commit();
                       return true;
                       } 
           catch(PDOException $e){ 
                       if($con->inTransaction()){
                           $con->rollBack();
                       }
                       throw $e;
                                   }
                               }


          



                               
                    function viewborrowers(){
                        $con = $this->opencon();
                        return $con->query("SELECT * FROM borrowers")->fetchAll();
                    }
  
    function viewBooks() {
        $con = $this->opencon();
        $query = "
            SELECT 
                books.book_id, 
                books.book_title, 
                books.book_isbn, 
                books.book_publication_year, 
                books.book_publisher,
                (SELECT COUNT(*) FROM bookcopy WHERE bookcopy.book_id = books.book_id) as total_copies,
                (SELECT COUNT(*) FROM bookcopy WHERE bookcopy.book_id = books.book_id AND bookcopy.status = 'AVAILABLE') as available_copies
            FROM books
        ";
        return $con->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

  
    function viewAuthors() {
        $con = $this->opencon();
        return $con->query("SELECT * FROM authors")->fetchAll(PDO::FETCH_ASSOC);
    }


    function viewGenres() {
        $con = $this->opencon();
        return $con->query("SELECT * FROM genres")->fetchAll(PDO::FETCH_ASSOC);
    }

   
    function insertBookCopy($book_id, $status) {
        $con = $this->opencon();
        $stmt = $con->prepare("INSERT INTO bookcopy(book_id, status) VALUES(?, ?)");
        return $stmt->execute([$book_id, $status]);
    }


    function insertBookAuthor($book_id, $author_id) {
        $con = $this->opencon();
        $stmt = $con->prepare("INSERT INTO bookauthors(book_id, author_id) VALUES(?, ?)");
        return $stmt->execute([$book_id, $author_id]);
    }

   
    function insertBookGenre($book_id, $genre_id) {
        $con = $this->opencon();
        $stmt = $con->prepare("INSERT INTO bookgenre(book_id, genre_id) VALUES(?, ?)");
        return $stmt->execute([$book_id, $genre_id]);
    }
          function delete_book($book_id) {
    $con = $this->opencon(); 
    try {
        $stmt = $con->prepare("DELETE FROM books WHERE book_id = ?");
        return $stmt->execute([$book_id]);
    } catch (PDOException $e) {
        throw $e;
    }
}

    function updateBook($book_id, $title, $isbn, $year, $publisher)
{
    $con = $this->opencon();
 
    try {
        $con->beginTransaction();
 
        $stmt = $con->prepare("
            UPDATE books
            SET book_title = ?,
                book_isbn = ?,
                book_publication_year = ?,
                book_publisher = ?
            WHERE book_id = ?
        ");
 
        $stmt->execute([$title, $isbn, $year, $publisher, $book_id]);
 
        $con->commit();
        return true; // Successfully updated
 
    } catch (PDOException $e) {
        if ($con->inTransaction()) {
            $con->rollBack();
        }
        throw $e;
    }


}}
?>