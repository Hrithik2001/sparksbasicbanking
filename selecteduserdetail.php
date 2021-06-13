<?php
include 'config.php';

if(isset($_POST['submit']))
{
    $from = htmlentities($_GET['id']);
    $to = htmlentities($_POST['to']);
    $amount = htmlentities($_POST['amount']);

    $stmt = $pdo->prepare("
      SELECT * FROM users WHERE id = :id
    ");
    $stmt->execute([
      ':id' => $from,
    ]);
    $result = [];
    while ( $row = $stmt->fetch(PDO::FETCH_OBJ) )
    {
        $result[] = $row;
    }
    $resultLen = count($result);

    $stmt1 = $pdo->prepare("
      SELECT * FROM users WHERE id = :id
    ");
    $stmt1->execute([
      ':id' => $to,
    ]);
    $result1 = [];
    while ( $row = $stmt1->fetch(PDO::FETCH_OBJ) )
    {
        $result1[] = $row;
    }
    $resultLen1 = count($result1);

    // constraint to check input of negative value by user
    if (($amount)<0)
   {
        echo '<script type="text/javascript">';
        echo ' alert("Enter Valid Amount!!!!")';  // showing an alert box.
        echo '</script>';
    }



    // constraint to check insufficient balance.
    else if($amount > $result[0]->balance)
    {

        echo '<script type="text/javascript">';
        echo ' alert("Insufficient Balance!!!!")';  // showing an alert box.
        echo '</script>';
    }



    // constraint to check zero values
    else if($amount == 0){

         echo "<script type='text/javascript'>";
         echo "alert('Zero value cannot be transferred')";
         echo "</script>";
     }


    else {

                // deducting amount from sender's account
                $newbalance = $result[0]->balance - $amount;
                $sql = $pdo->prepare("
                  UPDATE users set balance = :balance where id = :id
                ");
                $sql->execute([
                  ':id' => $from,
                  ':balance' => $newbalance,
                ]);



                // adding amount to reciever's account
                $newbalance = $result1[0]->balance + $amount;
                $sql = $pdo->prepare("
                  UPDATE users set balance = :balance where id = :id
                ");
                $sql->execute([
                  ':id' => $to,
                  ':balance' => $newbalance,
                ]);

                $sender = $result[0]->name;
                $receiver = $result1[0]->name;
                $sql = $pdo->prepare("
                  INSERT INTO transaction(sender, receiver, balance) VALUES (:sender, :receiver, :balance)
                ");
                $sql->execute([
                  ':sender' => $sender,
                  ':receiver' => $receiver,
                  ':balance' => $amount,
                ]);

                if($sql){
                     echo "<script> alert('Transaction Successful');
                                     window.location='transactionhistory.php';
                           </script>";

                }

                $newbalance= 0;
                $amount =0;
        }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/table.css">
    <link rel="stylesheet" type="text/css" href="css/navbar.css">

    <style type="text/css">

		button{
			border:none;
			background: #d9d9d9;
		}
	    button:hover{
			background-color:#777E8B;
			transform: scale(1.1);
			color:white;
		}

    </style>
</head>

<body>

<?php
  include 'navbar.php';
?>

	<div class="container">
        <h2 class="text-center pt-4">Transaction</h2>
            <?php
                include 'config.php';
                $sid=htmlentities($_GET['id']);
                $sql = $pdo->prepare("
                  SELECT * FROM  users where id = :id
                ");
                $sql->execute([
                  ':id' => $sid,
                ]);
                if(!$sql)
                {
                    echo "Error : ".$sql."<br>".mysqli_error($pdo);
                }
                $rows = [];
                while ( $row = $sql->fetch(PDO::FETCH_OBJ) )
                {
                    $rows[] = $row;
                }
                $rowsLen = count($rows);
            ?>
            <form method="post" name="tcredit" class="tabletext" ><br>
        <div>
            <table class="table table-striped table-condensed table-bordered">
                <tr>
                    <th class="text-center">Id</th>
                    <th class="text-center">Name</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">Balance</th>
                </tr>
                <tr>
                    <td class="py-2"><?php echo $rows[0]->id ?></td>
                    <td class="py-2"><?php echo $rows[0]->name ?></td>
                    <td class="py-2"><?php echo $rows[0]->email ?></td>
                    <td class="py-2"><?php echo $rows[0]->balance ?></td>
                </tr>
            </table>
        </div>
        <br><br><br>
        <label>Transfer To:</label>
        <select name="to" class="form-control" required>
            <option value="" disabled selected>Choose</option>
            <?php
                include 'config.php';
                $sid=htmlentities($_GET['id']);
                $sql = $pdo->prepare("
                  SELECT * FROM  users where id != :id
                ");
                $sql->execute([
                  ':id' => $sid,
                ]);
                if(!$sql)
                {
                    echo "Error : ".$sql."<br>".mysqli_error($pdo);
                }
                $rows = [];
                while ( $row = $sql->fetch(PDO::FETCH_OBJ) )
                {
                    $rows[] = $row;
                }
                $rowsLen = count($rows);
                if(!$sql)
                {
                    echo "Error ".$sql."<br>".mysqli_error($sql);
                }
                for($i=1;$i<=$rowsLen;$i++):
            ?>
                <option class="table" value="<?php echo $rows[$i-1]->id;?>" >

                    <?php echo $rows[$i-1]->name ;?> 

                </option>
            <?php
          endfor;
            ?>
            <div>
        </select>
        <br>
        <br>
            <label>Amount:</label>
            <input type="number" class="form-control" name="amount" required>
            <br><br>
                <div class="text-center" >
            <button class="btn mt-3" name="submit" type="submit" id="myBtn">Transfer</button>
            </div>
        </form>
    </div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
</body>
</html>
