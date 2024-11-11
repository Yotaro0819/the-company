<?php
require_once("database.php");

class User extends Database {

// ----------------------------------------------------------------------------------
  public function store($request) {
    //option + shift + ↓
    //command + d
    //そのままcontrol + nでカーソル2個のまま下へ
      $first_name = $request['first_name'];
      $last_name = $request['last_name'];
      $username = $request['username'];
      $password = $request['password'];


      $password = password_hash($password, PASSWORD_DEFAULT);

      $sql = "SELECT * FROM users WHERE username = '$username'";
      $result = $this->conn->query($sql);
      // if文の返しが一文だった場合は{}つけなくても良い.
      if($result->num_rows == 1) die("Username is already taken");


        $sql = "INSERT INTO users (`first_name`, `last_name`, `username`, `password`) 
              VALUES ('$first_name', '$last_name', '$username', '$password')";
    
      if($this->conn->query($sql)) {
        header('location: ../views');  //go to index.php in views.
        exit;
      } else {
        die('Error creating the user' . $this->conn->error);
      }
  }
// ----------------------------------------------------------------------------------
  public function login($request) {
      $username = $request['username'];
      $password = $request['password'];


      $sql = "SELECT * FROM users WHERE username = '$username'";

      $result = $this->conn->query($sql);

      #Check the username
      if($result->num_rows == 1) {
        #Check the password is correct
        $user = $result->fetch_assoc();
        //$user = ['id' => 1, 'username' => 'naruto', 'password' => '$skqpsb208dff...(hashed already)'];

        if(password_verify($password, $user['password'])) {
            #Create session variables for future use
            session_start();
            $_SESSION['id'] = $user['id'];
            $_ESSSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['first_name'] . " " . $user['last_name'];

            header("location: ../views/dashboard.php");
            exit;
        } else {
          die('Password is incorrect');
        }
      } else {
        die('Username not found');
      }
  }
// ----------------------------------------------------------------------------------
  public function logout() {
    session_start();
    session_unset();
    session_destroy();
    
    header("location: ../views");
    exit;
  }
// ----------------------------------------------------------------------------------
  public function getAllUsers() {
    $sql = "SELECT * FROM users";

    if($result = $this->conn->query($sql)) {
      return $result;
    } else {
      die('Error retrieving all users: ' . $this->conn->error);
    }
  }
// ----------------------------------------------------------------------------------
  public function getUser($id) {
    $sql = "SELECT * FROM users WHERE id = $id";
    
    if($result = $this->conn->query($sql)) {
      return $result->fetch_assoc();
    } else {
      die('Error retrieving all users: ' . $this->conn->error);
    }
  }

  // ----------------------------------------------------------------------------------
  public function update($request, $files) {
    session_start();
    $id = $_SESSION['id'];
    $first_name = $request['first_name'];
    $last_name  = $request['last_name'];
    $username   = $request['username'];
    $photo      = $files['photo']['name'];
    $tmp_name   = $files['photo']['tmp_name'];

    $sql = "UPDATE users SET first_name = '$first_name', last_name = '$last_name', username = '$username'
            WHERE id = $id";

    if($this->conn->query($sql)) {
        $_SESSION['username']  = $username;
        $_SESSION['full_name'] = "$first_name $last_name";

        # If there is an uploaded photo, save it to the db nad save the file to images folder.
        if($photo) {
          $sql = "UPDATE users SET photo = '$photo' WHERE id = $id";
          $destination = "../assets/images/$photo";

            #save the image name to db
          if($this->conn->query($sql)) {
            #If successful
            #Save the file to images folder
            if(move_uploaded_file($tmp_name, $destination)) {
              header('location: ../views/dashboard.php');
              exit;
            } else {
              die('Error moving the photo.');
            }
          } else {
            #if fail
            die('Error uploading the photo: ' . $this->conn->error);
          }
        } 
        #elseにしない理由はtrueのときはこのif文を通り、falseの時はそもそもこのif文を無視するようにするため
        #この記述でも問題がなく、なおかつ記述が少なくなるので良い
        #この記述はよくみられるので慣れること。
          header('location: ../views/dashboard.php');
          exit;
    } else {
        die('Error updating the user: ' . $this->conn->error);
    }
  }

  public function delete() {
    session_start();
    $id = $_SESSION['id'];

    $sql = "DELETE FROM users WHERE id = $id";

    if($this->conn->query($sql)) {
      $this->logout();
    } else {
      die('Error deleting your account: ' . $this->conn->error);
    }
  }
}

?>