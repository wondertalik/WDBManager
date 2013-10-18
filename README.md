WDBManager
==========

```
namespace test;

require_once __DIR__ . '/WDBConnection.php';
require_once __DIR__ . '/WDBDataReader.php';
require_once __DIR__. '/exception/WDBException.php';
require_once __DIR__. '/exception/WDBPDOException.php';

use bitmaster\db\WDBConnection as WDBConnection;
use bitmaster\db\exception\WDBPDOException;
use bitmaster\db\exception\WDBException;

$config["dbhost"] = "localhost";
$config["dbuser"] = "works_db";
$config["dbpass"] = "u44871";
$config["dbname"] = "works_db";

$db = new WDBConnection('works_db', 'u44871', 'works_db');

try {
    $result = $db->createCommand()->select('login')->from('f5_users')->where('id=:id', array(':id' => 1))->query();
    $users = $result->readFetchAssoc();
} catch (WDBPDOException $e) {
    echo "current_line:".__LINE__."<br>".$e->getMessage();
} catch (WDBException $e) {
    echo "current_line:".__LINE__."<br>".$e->getMessage();
}

try {
    $sql = "select login from f5_users where id = :id";
    $result = $db->createCommand($sql)->query(array(':id' => 1));
    $result->read();
} catch (WDBPDOException $e) {
    echo "current_line:".__LINE__."<br>".$e->getMessage();
} catch (WDBException $e) {
    echo "current_line:".__LINE__."<br>".$e->getMessage();
}
```
