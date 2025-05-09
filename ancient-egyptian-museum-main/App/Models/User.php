<?php

namespace App\Models;

use PDO;

enum UserRole {

    case visitor;
    case member;
    case researcher;
    case volunteer;
    case patron;
    case admin;

}

class User  extends Model{



    
    public $id;
    public $name;
    public $email;
    public $password;
    public UserRole $role;
    private $db;


    public function __construct($db) {
        $this->db = $db;
    }

    public function register($name, $email, $password) {
        
        if(empty($name)||empty($email)||empty($password))
        {
            return false;
        }

        
           $query = $this->db->prepare("SELECT * FROM users WHERE email = :email");
           $query->bindParam(':email', $email);
           $query->execute();
           if ($query->rowCount() > 0) 
           {
            return false;
           }

           $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

           $query = $this->db->prepare("INSERT INTO users (name, email, password, role))
           VALUES (:name, :email, :password, :role)");

           $query->bindParam(':name', $name);
           $query->bindParam(':email', $email);
           $query->bindParam(':password', $hashedPassword);
           $query->bindParam(':role' );

           if ($query->execute()) {

            return true; 

        } else {

            
            return false;
        }
    }

            

    

    public function login($email, $password) {
        
        if(empty($email)||empty($password))
        {
            return false;
        }

        
         $query = $this->db->prepare("SELECT * FROM users WHERE email = :email");
         $query->bindParam(':email', $email);
         $query->execute();
         $user = $query->fetch(PDO::FETCH_ASSOC);
         
        if ($user && password_verify($password, $user['password'])) {

            $sessionId = bin2hex(random_bytes(16)); 
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['session_id'] = $sessionId;


            // ANAS, use the code below if you want to store the session in the database
            
// $query = $this->db->prepare("INSERT INTO sessions (user_id, session_id) VALUES (:user_id, :session_id)");
//             $query->bindParam(':user_id', $user['id']);
//             $query->bindParam(':session_id', $sessionId);
//             $query->execute();


        return $sessionId;
        }

        else{
            return false;
        }


    }

    public function logout($sessionId) {
        
        $query = $this->db->prepare("DELETE FROM sessions WHERE session_id = :session_id");
        $query->bindParam(':session_id', $sessionId);
        $query->execute();

        session_unset();
        session_destroy();

    }

    public function updateProfile($name, $email) {
        if (empty($name) || empty($email)) {
            return false;
        }
    
        $query = $this->db->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
        $query->bindParam(':name', $name);
        $query->bindParam(':email', $email);
        $query->bindParam(':id', $_SESSION['user_id']); // Assuming user is logged in
    
        return $query->execute();
    }
    

    public function changePassword($oldPass, $newPass) {
        if (empty($oldPass) || empty($newPass)) {
            return false;
        }
    
        // Fetch current password
        $query = $this->db->prepare("SELECT password FROM users WHERE id = :id");
        $query->bindParam(':id', $_SESSION['user_id']);
        $query->execute();
        $user = $query->fetch(PDO::FETCH_ASSOC);
    
        if (!$user || !password_verify($oldPass, $user['password'])) {
            return false; // Old password incorrect
        }
    
        // Update to new password
        $newHashed = password_hash($newPass, PASSWORD_BCRYPT);
        $update = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $update->bindParam(':password', $newHashed);
        $update->bindParam(':id', $_SESSION['user_id']);
    
        return $update->execute();
    }
    


}