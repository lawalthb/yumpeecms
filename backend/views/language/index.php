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
$this->title = 'Language Profiles';


$saveURL = \Yii::$app->getUrlManager()->createUrl('language/save');
$deleteURL = \Yii::$app->getUrlManager()->createUrl('language/delete');
$this->registerJs( <<< EOT_JS
       $(document).on('click', '#btnSubmit',
       function(ev) {  
        
        $.post(
            '{$saveURL}',$( "#frm1" ).serialize(),
            function(data) {
                alert(data);
                location.href='?r=language/index';
            }
        )
        ev.preventDefault();
  }); 
  
  $(document).on('click', '#btnNew',
       function(ev) {   
        location.href='?r=language/index';
        
        
  }); 
            
  $('.delete_event').click(function (element) {                    
                    var id = $(this).attr('id');
                    var event_name = $(this).attr('event_name');
                    if(confirm('Are you sure you want to delete - ' + event_name)){
                        $.get(  
                            '{$deleteURL}',{id:id},
                            function(data) {
                                alert(data);
                            }
                        )
                    }            
  });
                            
$("#datalisting").DataTable();   
EOT_JS
);  
?>

<style type="text/css" media="screen">
    #yumpee_widget_content { 
        position: relative;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0px;
        min-width:1000px;
        height:500px;
     
    }
</style>

      
<div class="container-fluid">
<button class="btn btn-info" data-toggle="collapse" data-target="#addCategories">Add Language</button>
<div id="addCategories">
     <form action="index.php?r=language/index" method="post" name="frm1" id="frm1">
    <table class="table">
        <tr><td>Name<td><input name="name" id="name" value="<?=$rs['name']?>" class="form-control" type="text" />
        <tr><td>Language Code<td><input name="code" id="code" value="<?=$rs['code']?>" class="form-control" type="text" />
        <tr><td>Description<td><textarea name="description" id="description" rows="2" cols="30" class="form-control"><?=$rs['description']?></textarea>
        
        <tr><td colspan="2"><button type="submit" id="btnSubmit" class="btn btn-success">Save</button> <button type="button" id="btnNew" class="btn btn-primary">New</button> <input type="hidden" name="processor" value="true" /><input type="hidden" name="id" value="<?=$rs['id']?>" />
            
            </td>
        
        
    </table>
    </form>
</div>


<div class="box">
<div class="box-body">
    <table id="datalisting" class="table table-bordered table-hover"><thead><tr><th>Name</th><th>Code</th><th>Description<th>Action</thead>
        <tbody>
<?php
    foreach($records as $record):
                
?>
    <tr><td><?=$record['name']?></td><td><?=$record['code']?><td><?=$record['description']?></td><td><a href='?actions=edit&id=<?=$record['id']?>&r=language/index'><small><i class="glyphicon glyphicon-pencil"></i></small></a> <a href='#' class='delete_event' id='<?=$record['id']?>' event_name='<?=$record['name']?>'><small><i class="glyphicon glyphicon-trash"></i></small></a> </td>
        
 <?php
        endforeach;
?>
        </tbody>
</table>
</div>
</div>
</div>
