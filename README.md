WDBManager
==========

namespace test;

require_once __DIR__ . '/new_db/WDBConnection.php';
require_once __DIR__ . '/new_db/WDBDataReader.php';

use bitmaster\db\WDBConnection as WDBConnection;
use bitmaster\db\exception\WDBPDOException;

$config["dbhost"] = "localhost";
$config["dbuser"] = "works_db";
$config["dbpass"] = "u44871";
$config["dbname"] = "works_db";

$db = new WDBConnection($config['dbuser'], $config['dbpass'], $config['dbname'], $config['dbhost']);

try {
    $result = $db->createCommand()
        ->select('login')
        ->from('f5_users')
        ->where('id=:id', array(':id' => 1))
        ->query();
} catch (WDBPDOException $e) {
    //обрабатываем исключения
}

try {

    $sql = "select login from f5_users where id = :id";
    $result = $db->createCommand($sql, ':id=>1')->query();
} catch (WDBPDOException $e) {
    //обрабатываем исключение
}
