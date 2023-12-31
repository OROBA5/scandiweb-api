<?php

/* include '../config/Database.php';
include '../connection.php';
include './Product.php'; */

class Furniture extends Product {
    // Declare furniture specific fields
    public $height;
    public $width;
    public $length;
    private $conn;

    // Declare constructor for the Furniture class
    public function __construct($id, $sku, $name, $price, $product_type_id, $height, $width, $length)
    {
        parent::__construct($id, $sku, $name, $price, $product_type_id);
        $this->height = $height;
        $this->width = $width;
        $this->length = $length;
    }

    // Setters and getters for the class specific fields
    public function getHeight()
    {
        return $this->height;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function setLength($length)
    {
        $this->length = $length;
    }

    function create()
    {
        // Establish database connection
        $database = new Database();
        $conn = $database->getConnection();

        // Insert data into the "product" table
        $productStmt = $conn->prepare("
            INSERT INTO product(`sku`, `name`, `price`, `product_type_id`)
            VALUES(?, ?, ?, ?)");

        $sku = $this->getSku();
        $name = $this->getName();
        $price = $this->getPrice();
        $productTypeId = $this->getProductTypeId();

        $sku = htmlspecialchars(strip_tags($sku));
        $name = htmlspecialchars(strip_tags($name));
        $price = htmlspecialchars(strip_tags($price));
        $productTypeId = htmlspecialchars(strip_tags($productTypeId));

        $productStmt->bind_param("ssii", $sku, $name, $price, $productTypeId);

        // Insert data into the "product" table first
        if ($productStmt->execute()) {
            // Get the generated product ID
            $product_id = $productStmt->insert_id;
            $productStmt->close();

            // Insert data into the "furniture" table
            $furnitureStmt = $conn->prepare("
                INSERT INTO furniture(`product_id`, `height`, `width`, `length`)
                VALUES(?, ?, ?, ?)");

            $height = $this->getHeight();
            $width = $this->getWidth();
            $length = $this->getLength();

            $furnitureStmt->bind_param("iddd", $product_id, $height, $width, $length);

            // Execute the furniture query
            if ($furnitureStmt->execute()) {
                $furnitureStmt->close();
                $conn->close();
                return true;
            }

            $furnitureStmt->close();
        }

        $productStmt->close();
        $conn->close();
        return false;
    }

    function read($conn) {
        if ($this->id) {
            $stmt = $conn->prepare("
                SELECT f.*, p.sku, p.name, p.price, p.product_type_id
                FROM furniture f
                INNER JOIN product p ON f.product_id = p.id
                WHERE f.product_id = ?
            ");
            $stmt->bind_param("i", $this->id);
        } else {
            $stmt = $conn->prepare("
                SELECT f.*, p.sku, p.name, p.price, p.product_type_id
                FROM furniture f
                INNER JOIN product p ON f.product_id = p.id
            ");
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    
        return $result;
    }
    
    function delete($conn) {
        $productId = $this->getId();

        // Delete the book entry
        $deleteFurnitureStmt = $conn->prepare("DELETE FROM furniture WHERE product_id = ?");
        $deleteFurnitureStmt->bind_param("i", $productId);
        $deleteFurnitureStmt->execute();
        $deleteFurnitureStmt->close();

        // Call the delete() method of the parent class (Product)
        parent::delete($conn);

    }

    
}

