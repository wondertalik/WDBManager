WDBManager
==========

```
namespace test;

require_once __DIR__ . '/WDBConnection.php';

use bitmaster\db;

try {

    $db = new db\WDBConnection('works_db', 'u44871', 'works_db');
    $result = $db->createCommand()->select('login')->from('f5_users')->where('id=:id', array(':id' => 1))->query();
    print_r($users = $result->readFetchAssoc());
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
