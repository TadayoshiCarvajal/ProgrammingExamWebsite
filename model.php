<?php
    /* Connect to njit sql server
    
    The actual database login credentials have been replaced with generic strings for security reasons.
    */

    $dbhost = 'database';
    $dbuser = 'username';
    $dbpass = 'password';
    $dbname = 'database name';
    $db = new mysqli("$dbhost", "$dbuser", "$dbpass", "$dbname");
    $credentials_encoded = file_get_contents('php://input');
    $credentials_decoded = json_decode($credentials_encoded, true);
    mysqli_set_charset($db, "utf8");

/*
This query is used to put a question into the database.

USE:
curl -d '{"queryType":"putQuestion","questionIDKey":1391282,"topicKey":"Loops", "difficultyKey":"Easy", "funcNameKey":"sum", "questionBodyKey":"body",\
    "testcase1Key":"case1","testcase2Key":"case2","testcase3Key":"case3", "pointsKey":3.0}' -X POST https://web.njit.edu/~tc95/CS490/beta/model.php
*/
if ($credentials_decoded['queryType'] == "putQuestion") { 
    $id = $credentials_decoded['questionIDKey'];
    $bid = 1;
    $topic = $credentials_decoded['topicKey'];
    $diff = $credentials_decoded['difficultyKey'];
    $body = $credentials_decoded['questionBodyKey'];
    $fname = $credentials_decoded['funcNameKey'];
    $tc1 = $credentials_decoded['testcase1Key']; 
    $tc2 = $credentials_decoded['testcase2Key'];
    $tc3 = $credentials_decoded['testcase3Key'];
    $tc4 = $credentials_decoded['testcase4Key'];
    $tc5 = $credentials_decoded['testcase5Key'];
    
$sql = "INSERT INTO question(q_id,b_id,topic,difficulty,body,testcase1,testcase2,testcase3,testcase4,testcase5,funcName) 
            VALUES (" . $id . ", " . $bid . ", '" . $topic . "', '" . $diff . "', '" . $body . "', '" . $tc1 . "', '" . $tc2 . "', '" . $tc3 . "', '". $tc4 ."', '". $tc5 ."', '". $fname ."');";
    $result = $db->query($sql);

    if (!$result) {
        echo "Could not successfully run query ($sql) from DB.\n";
    }
    else {
        echo "SUCCESS\n";
    }
}

/*
This query is used to fetch all of the questions from the database

USE:
curl -d '{"queryType":"getAllQuestions"}' -X POST https://web.njit.edu/~tc95/CS490/beta/model.php
*/
else if ($credentials_decoded['queryType'] == "getAllQuestions") {

    $sql = "SELECT * FROM question;";
    $result = $db->query($sql);

    if (!$result) {
        echo "Could not successfully run query ($sql) from DB: " . mysql_error() . "\n";
    }
    else {
    	$rtn = array();
        while ( $row = $result->fetch_assoc() ) {
	      $rtn[]  = [ 'questionIdKey' => $row['q_id'], 'topicKey' => $row['topic'], 'funcNameKey' => $row['funcName'], 'difficultyKey' => $row['difficulty'], 'questionBodyKey' => $row['body'], 'testcase1Key' => $row['testcase1'], 'testcase2Key' => $row['testcase2'], 'testcase3Key' => $row['testcase3'], 'testcase4Key' => $row['testcase4'], 'testcase5Key' => $row['testcase5']];
	}
	echo json_encode($rtn);
    }
}

/*
This query is used to return a question from the database

USE:
curl -d '{ "queryType" : "getQuestion", "questionIDKey" : 1 }' -X POST https://web.njit.edu/~tc95/CS490/beta/model.php
*/
else if ($credentials_decoded['queryType'] == "getQuestion") {
    $sql = "SELECT * FROM question WHERE q_id = " . $credentials_decoded['questionIDKey'] . ";";
    $result = $db->query($sql);
    
    if (!$result) {
        echo "Could not successfully run query ($sql) from DB: " . mysql_error() . "\n";
    }
    else {
    	 $row = $result->fetch_assoc();
	 $rtn = array();
	 if ($row) {
	     $rtn = [ 'questionIdKey' => $row['q_id'], 'topicKey' => $row['topic'], 'funcNameKey' => $row['funcName'], 'difficultyKey' => $row['difficulty'], 'questionBodyKey' => $row['body'], 'testcase1Key' => $row['testcase1'], 'testcase2Key' => $row['testcase2'], 'testcase3Key' => $row['testcase3'], 'testcase4Key' => $row['testcase4'], 'testcase5Key' => $row['testcase5']];
	 } 
         echo json_encode($rtn);
    } 
}

/*
This query is used to remove a question from the database.

USE:
curl -d '{ "queryType" : "deleteQuestion", "questionIDKey" : -1591065772 }' -X POST https://web.njit.edu/~tc95/CS490/beta/model.php
*/
else if ($credentials_decoded['queryType'] == "deleteQuestion") {
    $sql = "DELETE FROM question WHERE q_id = " . $credentials_decoded['questionIDKey'] . ";";
    $result = $db->query($sql);

    if (!$result) {
        echo "Could not successfully run query ($sql) from DB: " . mysql_error() . "\n";
    }
    else {
         echo "SUCCESS\n";
    }
}

/*
This query is used to insert and exam into the database.

USE:
curl -d '{ "queryType":"putExam", "examNameKey":"The SAT", "classIDKey":"Transfiguration", "questionIDKeys":  [-1545329741,1794098166,1328391282], "pointsKey":[20,20,20],"gradeKey": 100.0 }' -X POST https://web.njit.edu/~tc95/CS490/beta/model.php
*/
else if ($credentials_decoded['queryType'] == "putExam") {
    $name = $credentials_decoded['examNameKey'];
    $tid = 15; # 3/23/18: THIS NEEDS TO BE UPDATED TO ALLOW N TEACHERS. 
    $class_id = $credentials_decoded['classIDKey'];
    $qids = $credentials_decoded['questionIDKeys'];
    $pts = $credentials_decoded['pointsKey'];
    $class = $credentials_decoded['classIDKey'];
    $points = $credentials_decoded['pointsKey'];
    $grade = $credentials_decoded['gradeKey'];
    
    // Create the exam.
    $sql = "INSERT INTO exam(t_id,name,cname,points) VALUES (" . $tid . ", '" . $name . "', '". $class ."', ". $grade .");";
    $result1 = $db->query($sql);
    if (!$result1) {
        echo "Exam with that name already exists!";
        #echo "Could not successfully run query ($sql) from DB.\n";
    }
    // Create the exam sessions.
    $eid = mysqli_insert_id ($db);
    // Get all the students in a class.
    $sql = "SELECT DISTINCT user FROM enrolled WHERE class_id = '" . $class_id ."' ;";
    // For each student, insert a session.
    $result = $db->query($sql);

    #echo sizeof($result);    
    while($row = $result->fetch_assoc()) {
        $stid = $row['user'];
        #echo $i . " username: " . $stid . "\n";
        $sql = "INSERT INTO session(e_id,st_id) VALUES (" . $eid . ", '" . $stid . "');";
        $result2 = $db->query($sql);
        if (!$result2) {
            echo "Could not successfully run query ($sql) from DB.\n";
            continue;
        }

        // Create the exam questions.
        $sid = mysqli_insert_id ($db);
        $i = 0;
        foreach($qids as $qid ){
            $sql1 = "INSERT INTO answer(s_id, q_id, points) VALUES (" . $sid . ", " . $qid . ", '".$points[$i]."');";
            $result3 = $db->query($sql1);
            $i += 1;
            if (!$result3) {
                echo "Could not successfully run query ($sql1) from DB.\n";
                break;
            }
        }
    }
    if ($result1 && $result2 && $result3) {
        echo "SUCCESS\n";
    }
}


/*
This query is used to fetch all exams from the database
*/
else if ($credentials_decoded['queryType'] == "getAllExams") {
    $sql1 = "SELECT * FROM session, exam GROUP BY exam.e_id;";
    $result1 = $db->query($sql1);
    $mainrtn = array();
    if (!$result1) {
        echo "Could not successfully run query ($sql1) from DB: " . mysqli_error() . "\n";
        exit;
    }   
    while( $row = $result1->fetch_assoc() ) {
        $rtn = $rtn = [ 'classIDKey' => $row['cname'], 'examNameKey' => $row['name'], 'gradeKey' => $row['points']];
        $sid = $row['s_id'];
        $sql2 = "SELECT * FROM answer WHERE s_id = " . $sid . ";";
        $result2 = $db->query($sql2);
        if (!$result2) {
            echo "Could not successfully run query ($sql2) from DB.\n";
        }
        else {
            $qids = array();
            while ( $row = $result2->fetch_assoc() ) {
                $qids[]  = $row['q_id'];
            }
            $rtn += array('questionIDKeys' => $qids);
        }
        $mainrtn[] = $rtn;
    }
    echo json_encode($mainrtn); 
}

/*
This query is used to fetch a single exam from the database.

USE:
curl -d '{ "queryType":"getExam", "classIDKey":"Transfiguration"}' -X POST https://web.njit.edu/~tc95/CS490/beta/model.php
*/
else if ($credentials_decoded['queryType'] == "getExam") {
    $class = $credentials_decoded['classIDKey'];
    $sql1 = "SELECT * FROM session, exam WHERE session.e_id = exam.e_id AND exam.cname = '". $class ."';";
    
    $result1 = $db->query($sql1);
    if (!$result1) {
        echo "Could not successfully run query ($sql1) from DB: " . mysqli_error() . "\n";
    }
    
    while( $row = $result1->fetch_assoc() ) {
        $rtn = [ 'examNameKey' => $row['name'], 'gradeKey' => $row['points']];
    	$sid = $row['s_id']; 
    	$sql2 = "SELECT * FROM answer WHERE s_id = " . $sid . ";";
    	$result2 = $db->query($sql2);
    	if (!$result2) {
           echo "Could not successfully run query ($sql2) from DB.\n";
    	}
	else {
            $qids = array();
       	    while ( $row = $result2->fetch_assoc() ) {
                $qids[]  = $row['q_id'];
            }
	    $rtn += array('questionIDKeys' => $qids);
    	}
    }
    echo json_encode($rtn);
}

/*
This query is used to remove an exam from the database.

Use:
curl -d '{ "queryType" : "deleteExam" , "examNameKey" : "CSS 288 Spring Exam" }' -X POST https://web.njit.edu/~tc95/CS490/beta/model.php
*/
else if ($credentials_decoded['queryType'] == "deleteExam") {
    $sql = "DELETE FROM exam WHERE name = '" . $credentials_decoded['examNameKey'] . "';";
    $result = $db->query($sql);
    if (!$result) {
        echo "Could not successfully run query ($sql) from DB.\n";
    }
    else {
         echo "SUCCESS\n";
    }
}

/*
This query is used to place an answer into the database.

USE:
curl -d '{"queryType":"putAnswers", "questionIDKeys": [1794098166,-1545329741], "answerKeys": ["a","b"],"classIDKey":"The SAT" ,"studentIDKey":"Draco Malfoy"}' -X POST https://web.njit.edu/~tc95/CS490/beta/model.php
*/
else if ($credentials_decoded['queryType'] == "putAnswers") {

    $st_id = $credentials_decoded['studentIDKey'];
    $qids = $credentials_decoded['questionIDKeys'];
    $answers = $credentials_decoded['answerKeys'];
    $class = $credentials_decoded['classIDKey'];
    
    $sql = "SELECT DISTINCT session.s_id FROM session, answer, exam WHERE session.s_id = answer.s_id AND session.e_id = exam.e_id AND exam.cname = '". $class ."' AND session.st_id = '" . $st_id . "';";
    $result1 = $db->query($sql);
    if (!$result1) {
        echo "Could not successfully run query ($sql) from DB.\n";
    }
    $row = $result1->fetch_assoc();
    $sid = $row['s_id'];
    
    for ($i = 0; $i < count($qids); $i++) {
        $qid = $qids[$i];
	    $answer = $answers[$i];
	        $sql = "UPDATE answer SET answer = '" . $answer . "' WHERE s_id = " . $sid . " AND q_id = " . $qid . ";";
        $result2 = $db->query($sql);
	    if (!$result2) {
            echo "Could not successfully run query ($sql) from DB.\n";
    	    }
    }
    if ($result2) {
        echo "SUCCESS";
    }
    $sql = "UPDATE session SET submitted = True WHERE session.st_id = '" . $st_id . "';";

    $result3 = $db->query($sql);
    if (!$result3) {
        echo "Could not successfully run query ($sql) from DB.\n";
    }
}

/*
This query is used to fetch all answers from the database for a specified student. For a specified class.

USE:
curl -d '{"queryType":"getAnswers", "classIDKey":"Transfiguration", "studentIDKey":"Draco Malfoy"}' -X POST https://web.njit.edu/~tc95/CS490/beta/model.php
*/
else if ($credentials_decoded['queryType'] == "getAnswers") {
    $st_id = $credentials_decoded['studentIDKey'];
    $cname = $credentials_decoded['classIDKey'];
    $sql = "SELECT DISTINCT q_id, answer, answer.points FROM session, answer, exam WHERE exam.e_id = session.e_id AND session.st_id = '" . $st_id . "' AND session.s_id = answer.s_id AND exam.cname = '". $cname ."';";
    $result = $db->query($sql);
    if (!$result) {
        echo "Could not successfully run query ($sql) from DB.\n";
    }
    $rtn = array("answerKeys" => array(), "pointsKeys" => array());
    while ( $row = $result->fetch_assoc() ) {
        #echo '\n'. var_dump($row) . '\n';
	$rtn['answerKeys'][$row['q_id']] = $row['answer'];
	$rtn['pointsKeys'][$row['q_id']] = $row['points'];
    }
    echo json_encode($rtn);
}

/*
This query is used to get all of the student IDs from the database.

USE:
curl -d '{"queryType":"getAllStudentIDKeys"}' -X POST https://web.njit.edu/~tc95/CS490/beta/model.php
*/
else if ($credentials_decoded['queryType'] == "getAllStudentIDKeys") {
    $sql = "SELECT user FROM login WHERE status = 1;";
    $result = $db->query($sql);
    if (!$result) {
        echo "Could not successfully run query ($sql) from DB.\n";
	exit;   
    }
    $rtn = array();
    while ( $row = $result->fetch_assoc() ) {
        $rtn[] = $row['user'];
    }

    echo json_encode($rtn);
}

/*
This query is used to place a grade for an exam into the database.

USE:
curl -d '{"queryType" : "putGrade" , "classIDKey" : "Transfiguration", "studentIDKey" : "Draco Malfoy", "feedBack" : ["you did this wrong","no dont use a loop"], "questionIDKeys":[1794098166,-1545329741], "scoresKeys" : [0.5, 0.75], "gradeKey": 90.0}' -X POST https://web.njit.edu/~tc95/CS490/beta/model.php
*/
else if ($credentials_decoded['queryType'] == "putGrade") {
    if (array_key_exists('iFeedBack', $credentials_decoded)) {
        $ifeedbacks = $credentials_decoded['iFeedBack'];
    	echo "test";
    }
    else {
    	 echo 'other test';
    }
    if (array_key_exists('gradesReleased', $credentials_decoded)) {
        $GR = $credentials_decoded['gradesReleased'];
    }
    $st_id = $credentials_decoded['studentIDKey'];
    $qids = $credentials_decoded['questionIDKeys'];
    $feedbacks = $credentials_decoded['feedBack'];
    $scores = $credentials_decoded['scoresKeys'];
    $grade = $credentials_decoded['gradeKey'];
    $cname = $credentials_decoded['classIDKey'];
    
    $sql1 =    "SELECT DISTINCT session.s_id 
                FROM session, answer, exam 
                WHERE session.s_id = answer.s_id AND session.e_id = exam.e_id AND exam.cname = '". $cname ."' AND session.st_id = '" . $st_id . "';";
    $result1 = $db->query($sql1);
    if (!$result1) {
        echo "Could not successfully run query ($sql1) from DB.\n";
    }
    $row = $result1->fetch_assoc();
    $sid = $row['s_id'];
    for ($i = 0; $i < count($qids); $i++) {
        $qid = $qids[$i];
        $feedback = $feedbacks[$i];
        $score = $scores[$i];
        if (array_key_exists('iFeedBack', $credentials_decoded)) {
            $ifeedback = $ifeedbacks[$i];
            $sql2 = "UPDATE answer SET score = " . $score . ", ifeedback = '". $ifeedback ."' WHERE s_id = " . $sid . " AND q_id = " . $qid . ";";
            echo $ifeedback;
	}
        else {
	     $sql2 = "UPDATE answer SET score = " . $score . ", feedback =  '" . $feedback . "' WHERE s_id = " . $sid . " AND q_id = " . $qid . ";";
	}
        $result2 = $db->query($sql2);
        if (!$result2) {
            echo "Could not successfully run query ($sql2) from DB.\n";
        }
    }
    if (array_key_exists('gradesReleased', $credentials_decoded)) {
        $sql3 = "UPDATE session SET grade = " . $grade . ", released = '". $GR ."' WHERE s_id = " . $sid . ";";
    }
    else {
    	$sql3 = "UPDATE session SET grade = " . $grade . " WHERE s_id = " . $sid . ";";
    }
    $result3 = $db->query($sql3);
    if ($result1 && $result2 && $result3) {
        echo "SUCCESS\n";
    }
}

/*
This query is used to get a grade for an exam from the database.

USE:
curl -d '{"queryType" : "getGrade" , "classIDKey" : "Transfiguration" , "studentIDKey" : "Draco Malfoy"}' -X POST https://web.njit.edu/~tc95/CS490/beta/model.php
*/
else if ($credentials_decoded['queryType'] == "getGrade") {
    $st_id = $credentials_decoded['studentIDKey'];
    $cname = $credentials_decoded['classIDKey'];
    
    $sql1 =    "SELECT DISTINCT session.s_id 
                FROM session, answer, exam
                WHERE session.s_id = answer.s_id AND session.st_id = '" . $st_id . "' AND exam.e_id = session.e_id AND exam.cname = '". $cname ."';";
                
    $result1 = $db->query($sql1);
    if (!$result1) {
        echo "Could not successfully run query ($sql1) from DB.\n";
	exit;
    }
    $row = $result1->fetch_assoc();
    $sid = $row['s_id'];
    
    $sql2 = "SELECT grade, released FROM session WHERE s_id = " . $sid . ";";
    $result2 = $db->query($sql2);
    if (!$result2) {
        echo "Could not successfully run query ($sql2) from DB.\n";
	exit;
    }
    $row = $result2->fetch_assoc();
    $grade = $row['grade'];
    $released = $row['released'];
    $rtn = array();
    $feedbacks = array();
    $ifeedbacks = array();
    $scores = array();

    $sql3 = "SELECT score, feedback,ifeedback FROM answer WHERE s_id = " . $sid . ";";
    $result3 = $db->query($sql3);
    if (!$result3) {
        echo "Could not successfully run query ($sql3) from DB.\n";
        exit;
    }
    while ($row = $result3->fetch_assoc()) {
        $feedbacks[] = $row['feedback'];
	$scores[] = $row['score'];	  
	$ifeedbacks[] = $row['ifeedback'];
    }
    $rtn['feedBack'] = $feedbacks;
    $rtn['iFeedBack'] = $ifeedbacks;
    $rtn['scoresKeys'] = $scores;
    $rtn['gradeKey'] = $grade;
    $rtn['gradesReleased'] = $released;

    if ($result1 && $result2 && $result3) {
        echo json_encode($rtn);
    }
}

/*
This query is used to login.

USE:
curl -d '{"queryType" : "login", "username" : "username", "password" : "password"}' -X POST https://web.njit.edu/~tc95/CS490/beta/model.php
*/

else if ($credentials_decoded['queryType'] == "login") {
    $user = $credentials_decoded['username'];
    $pass = $credentials_decoded['password'];
 
    $rtn = array();
    $sql = "SELECT * FROM login WHERE user = '" . $user . "';";
    $result = $db->query($sql);
    
    if (!$result) {
        echo "Could not successfully run query ($sql) from DB.";
    	exit;
    }
    else if (mysqli_num_rows($result) == 0) {
        $rtn['loginKey'] = "FAILED";
        echo json_encode($rtn);
        exit;
    }
    else if (mysqli_num_rows($result) == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['pass'])) {
            $status = $row['status'];
	    if ($status == 1) {
	        $rtn['loginKey'] = "STUDENT SUCCESS";
	        echo json_encode(["loginKey"=>"STUDENT SUCCESS"]);
	    }
	    else {
	        $rtn['loginKey'] = "TEACHER SUCCESS";
	    	echo json_encode($rtn); 
	    }
        }
        else {
	    $rtn['loginKey'] = "FAILED";
            echo json_encode($rtn);
            exit;
        }
    }
}
/*
This query is used to get the classes belonging to a user.

USE:
curl -d '{"queryType" : "getClasses", "username" : "username"}' -X POST https://web.njit.edu/~tc95/CS490/beta/model.php
*/
else if ($credentials_decoded['queryType'] == "getClasses") {
    $user = $credentials_decoded['username'];
    $rtn = array();
    $sql = "SELECT * FROM class WHERE teacher = '" . $user . "';";
    $result = $db->query($sql);
    
    if (!$result) {
        echo "Could not successfully run query ($sql) from DB.";
    	exit;
    }
    $classes = array();
    while ( $row = $result->fetch_assoc() ) {
        $classes[] = $row['name'];
    }
    $rtn['classesKeys'] = $classes;
    foreach($classes as $class) {
        $class_list = array();
        $sql = "SELECT * FROM enrolled WHERE class_id = '" . $class . "';";
        $result = $db->query($sql);
        while ( $row = $result->fetch_assoc() ) {
	    $class_list[] = $row['user'];
        }
        $rtn[$class] = $class_list;
    }
    
    echo json_encode($rtn);
}

    //$result1->close();
    $db->close();
?>  