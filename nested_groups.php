<?php

// Подключение к базе данных
$servername = "localhost:8000";
$username = "Hooman";
$password = "Hooman";
$dbname = "database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Функция для получения списка товаров в группе
function getProductsInGroup($group_id) {
    global $conn;
    $products = array();

    // Запрос на получение товаров в группе
    $sql = "SELECT id, name FROM products WHERE id_group = $group_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }

    return $products;
}

// Функция для рекурсивного получения списка групп и товаров
function getGroupsWithProducts($parent_id = 0) {
    global $conn;
    $output = '';

    // Запрос на получение групп товаров
    $sql = "SELECT id, name FROM groups WHERE id_parent = $parent_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $output .= '<ul>';
        while($row = $result->fetch_assoc()) {
            $group_id = $row['id'];
            $group_name = $row['name'];

            // Получаем товары в текущей группе
            $products = getProductsInGroup($group_id);

            // Формируем HTML для текущей группы и ее товаров
            $output .= '<li><a href="?group=' . $group_id . '">' . $group_name . '</a> (' . count($products) . ')';
            if (!empty($products)) {
                $output .= '<ul>';
                foreach ($products as $product) {
                    $output .= '<li>' . $product['name'] . '</li>';
                }
                $output .= '</ul>';
            }

            // Рекурсивный вызов для получения подгрупп
            $output .= getGroupsWithProducts($group_id);
            $output .= '</li>';
        }
        $output .= '</ul>';
    }

    echo $output; // Выводим результаты в конце скрипта

    return $output;
}

// Получение id выбранной группы, если она указана в GET-параметре
$selected_group = isset($_GET['group']) ? intval($_GET['group']) : 0;

// Генерация HTML для списка групп и товаров
$html_output = getGroupsWithProducts();

// Закрытие соединения с базой данных
$conn->close();
