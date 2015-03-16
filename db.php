<html>
<head>
<title>PHP Test</title>
<body>
<?php
        try {
            $db = new PDO('mysql:host=localhost;dbname=test;', 'root', 'youri');
            echo 'Connection succeeded <br/><br/>';
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
        
        // sql.1 number of students.
        function getStudentNumber($conn) {
                $sql = 'SELECT count(Matricule) as etunum from etudiants';
                $num = $conn->query($sql);
                while ($res = $num->fetch()) {
                        echo $res['etunum'];
                }
        }

        getStudentNumber($db);
        
           $db = null;
?>
</body>
</html>
