<?php

include('conn.php');

// for maintainece mode
$sql1 ="select * from onoff where id=11";
$result1 = mysqli_query($conn, $sql1);
$userDetails1 = mysqli_fetch_assoc($result1);

// for ftext and status
$sql2 ="select * from _ftext where id=1";
$result2 = mysqli_query($conn, $sql2);
$userDetails2 = mysqli_fetch_assoc($result2);
?>

<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-lg-12">
        <?= $this->include('Layout/msgStatus') ?>
    </div>
    
    <!----><!----><!----><!----><!----><!----><!----><!----><!----><!---->
     <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header h6 p-3 bg-success text-white">
                Mod Server Online_Offline
            </div>
            <div class="card-body">
                <?= form_open() ?>

                <input type="hidden" name="status_form" value="1">
                <div class="form-group mb-3">
                    <label for="status">Current Status: <font size="2" color ="#a39c9b"><?php echo $userDetails1['status']; ?></font></label>
                  
                  
    <div class="input-group mb-3">
  <div class="input-group-prepend">
    <div class="input-group-text">
        
      <input type="radio" id="radio" name="radios" value="1" aria-label="Checkbox for following text input" REQUIRED><font size="2"> Online Server</font>
      
    </div>
 </div>
 </div>

 
   <div class="input-group mb-3">
  <div class="input-group-prepend">
    <div class="input-group-text">
        
      <input type="radio" id="radio" name="radios" value="2" aria-label="Checkbox for following text input"><font size="2" REQUIRED> Offline Server</font>
      
    </div>
    </div>
    </div>
    <label for="modname">Current_Offline_Msg: <font size="2" color ="#a39c9b"><?php echo $userDetails1['myinput']; ?></font></label>


  <div class="input-group mb-3">
  <div class="input-group-prepend">
      
    <span class="input-group-text" id="inputGroup-sizing-default">Offline Msg</span>
  </div>
 
      <textarea class="form-control" placeholder="Enter Server Off Message" name = "myInput" id="myInput" id="exampleFormControlTextarea1" rows="1"></textarea>
</div>
                  
                    
                    <?php if ($validation->hasError('modname')) : ?>
                        <small id="help-modname" class="text-danger"><?= $validation->getError('modname') ?></small>
                    <?php endif; ?>
                </div>
                <div class="form-group my-2">
                    <button type="submit" class="btn btn-outline-success">Update Status</button>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
   
    <!----><!----><!----><!----><!----><!----><!----><!---->
    
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header h6 p-3 bg-danger text-white">
                Change Mod Name
            </div>
            <div class="card-body">
                <?= form_open() ?>

                <input type="hidden" name="modname_form" value="1">
             
                <div class="form-group mb-3">
                    <label for="modname">Current Mod Name: <font size="2" color ="#a39c9b"><?php echo $row['modname']; ?></font></label>
                    <input type="text" name="modname" id="modname" class="form-control mt-2" placeholder="Enter New Mod Name" aria-describedby="help-modname" REQUIRED>
                    <?php if ($validation->hasError('modname')) : ?>
                        <small id="help-modname" class="text-danger"><?= $validation->getError('modname') ?></small>
                    <?php endif; ?>
                </div>
                <div class="form-group my-2">
                    <button type="submit" class="btn btn-outline-danger">Update Mod Name</button>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
    
    <!----><!----><!----><!----><!----><!----><!----><!----><!----><!---->
    <!----><!----><!----><!----><!----><!----><!----><!----><!----><!---->
    
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header h6 p-3 bg-warning text-white">
                Change Mod Floating Text
            </div>
            <div class="card-body">
                <?= form_open() ?>

                <input type="hidden" name="_ftext" value="1">
                
                <label for="status">Current Mod Status: <font size="2" color ="#a39c9b"><?php echo $userDetails2['_status']; ?> </font> </label>
                
                    <div class="input-group mb-3">
  <div class="input-group-prepend">
    <div class="input-group-text">
        
      <input type="radio" id="radio" name="_ftextr" value="1" aria-label="Checkbox for following text input" REQUIRED><font size="2"> MOD STATUS - SAFE</font>
      
    </div>
 </div>
 </div>

 
   <div class="input-group mb-3">
  <div class="input-group-prepend">
    <div class="input-group-text">
        
      <input type="radio" id="radio" name="_ftextr" value="2" aria-label="Checkbox for following text input"><font size="2" REQUIRED> MOD STATUS - NOT SAFE</font>
      
    </div>
    </div>
    </div>
                
                <div class="form-group mb-3">
                    <label for="_ftext">Current Floating Text: <font size="2" color ="#a39c9b"><?php echo $userDetails2['_ftext']; ?></font></label>
                    <input type="text" name="_ftext" id="_ftext" class="form-control mt-2" placeholder="Enter New Floating Text" aria-describedby="help-_ftext" REQUIRED>
                    <?php if ($validation->hasError('_ftext')) : ?>
                        <small id="help-_ftext" class="text-black"><?= $validation->getError('_ftext') ?></small>
                    <?php endif; ?>
                </div>
                <div class="form-group my-2">
                    <button type="submit" class="btn btn-outline-warning">Update Floating Text </button>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
    
    <!----><!----><!----><!----><!----><!----><!----><!----><!----><!---->
</div>

<?= $this->endSection() ?>