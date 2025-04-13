<?php

function getCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllProductsWithCategory() {
    global $pdo;
    $stmt = $pdo->query("SELECT p.*, c.name AS category_name
                         FROM products p
                         LEFT JOIN categories c ON p.category_id = c.id
                         ORDER BY p.sort_order ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addProduct($data) {
    global $pdo;
    $sql = "INSERT INTO products (category_id, name, price, description, image_url, visible, sort_order)
            VALUES (:category_id, :name, :price, :description, :image_url, :visible, :sort_order)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':category_id' => $data['category_id'],
        ':name'        => $data['name'],
        ':price'       => $data['price'],
        ':description' => $data['description'],
        ':image_url'   => $data['image_url'],
        ':visible'     => $data['visible'],
        ':sort_order'  => $data['sort_order']
    ]);
}

function getProductById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateProduct($id, $data) {
    global $pdo;
    $sql = "UPDATE products SET category_id = :category_id, name = :name, price = :price, description = :description, 
            image_url = :image_url, visible = :visible, sort_order = :sort_order WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':category_id' => $data['category_id'],
        ':name'        => $data['name'],
        ':price'       => $data['price'],
        ':description' => $data['description'],
        ':image_url'   => $data['image_url'],
        ':visible'     => $data['visible'],
        ':sort_order'  => $data['sort_order'],
        ':id'          => $id
    ]);
}
