<?php

/* 
 * Author : Peter Odon
 * Email : peter@audmaster.com
 * Project Site : http://www.yumpeecms.com


 * YumpeeCMS is a Content Management and Application Development Framework.
 *  Copyright (C) 2018  Audmaster Technologies, Australia
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <https://www.gnu.org/licenses/>.

 */

use dosamigos\fileupload\FileUploadUI;
$this->title='Media';
$saveURL = \Yii::$app->getUrlManager()->createUrl('media/save');
$deleteURL = \Yii::$app->getUrlManager()->createUrl('media/delete');
$baseURL = Yii::getAlias('@image_dir/');

$this->registerJs( <<< EOT_JS
       
       $(document).on('click', '#btnSubmit',
       function(ev) {   
        
        $.post(
            '{$saveURL}',$( "#frm1" ).serialize(),
            function(data) {
                alert(data);
            }
        )
        ev.preventDefault();
  }); 
       
           
  $('.media').click(function (element) {   
      var selected_list="";
      var id = $(this).attr('id'); 
      var counter=0;
      $(".media").each(function() {
        counter++;
        id="c" + counter;
        
        if(document.getElementById(id).checked){
            selected_list = selected_list + "{$baseURL}/" + $(this).val() + " ";
        }
        
      });  
      
      document.cookie ="yumpee_image=" + selected_list;

            
                               
  });
            $("#datalisting").DataTable(); 
EOT_JS
);  
            
?>
<style>
    .images {
    border: double;
}

    </style>


<div class="container-fluid">
<ul class="nav nav-tabs">
  <li class="active"><a data-toggle="tab" href="#library">Select from library</a></li>
  <li><a data-toggle="tab" href="#media">Upload from your machine</a></li>
  
  
</ul>
  <div class="tab-content">
    <div id="media" class="tab-pane fade">
        <div><p>Click on the files you wish to upload to your library.</div>
        <p>
        <?= FileUploadUI::widget([
    'model' => $model,
    'attribute' => 'id',
    'url' => ['media/image-upload', 'id' => $id],
    'gallery' => false,
    'fieldOptions' => [
        'accept' => 'image/*'
    ],
    'clientOptions' => [
        'maxFileSize' => 8000000
    ],
    // ...
    'clientEvents' => [
        'fileuploaddone' => 'function(e, data) {
                                console.log(e);
                                console.log(data);
                            }',
        'fileuploadfail' => 'function(e, data) {
                                console.log(e);
                                console.log(data);
                            }',
        'fileuploadstop' => 'function(e){
            alert("File successfully uploaded");
            $(".modal").modal("toggle");
            
        }',
        
        'fileuploadsubmit'=> 'function(e, data) {
                                 
                                var empty_flds = 0;
                                $(".required").each(function() {
                                if(!$.trim($(this).val())) {
                                    empty_flds++;
                                    
                                }    
                                });
                                if(empty_flds > 0){
                                    alert("All alt tags must be filled");
                                    return false;
                                }
                                var input = $("#imagename");
                                var alttag=$("#alttag");
                                data.formData = {imagename:input.val(),alttag:alttag.val()};
                            }',
        'fileupload'=> 'function(e, data) {
                                
                            }'
    ],
]); ?>
        
        <style>
            .fa-large{
                font-size:230px;
            }
        </style>        
    </div>

<div id="library" class="tab-pane fade in active">
     


<div class="box">
<div class="box-body">
<table id="datalisting" class="table table-bordered table-striped">
    
    <tbody>
        
      <?php
      $counter=0;
      foreach($records as $user):
          $counter++;
          if(file_exists(Yii::getAlias('@uploads/uploads')."/".$user['path'])):
          $mime_type = mime_content_type(Yii::getAlias('@uploads/uploads')."/".$user['path']);          
          $mtype_ob = explode("/",$mime_type);
          if($mtype_ob[0]!=null):
              $type_file=$mtype_ob[0];
          endif;
          if($type_file=="image"):
      ?>
            <div class="col-md-2 images"><input type='checkbox' class="media" id='c<?=$counter?>' value='<?=$user['path']?>' imtype='image' document_name='<?=$user['name']?>'><img src='<?=$baseURL?>/<?=$user['path']?>' height='200px' align='top' width='200px' style='border:1px solid #233388' HSPACE='20' VSPACE='20'/><br><?=$user['name']?></div> 
      <?php
            else:
      ?>
           <div class="col-md-2 images"><input type='checkbox' class="media" id='c<?=$counter?>' value='<?=$user['path']?>' imtype='file' document_name='<?=$user['name']?>'><p><i class="fa fa-file fa-large" aria-hidden="true"></i><br><?=$user['name']?></div>  
      <?php
            endif;
            endif;
      endforeach;
     ?>
    </tbody>
</table>
</div>
</div>
</div>
</div>
</div>

