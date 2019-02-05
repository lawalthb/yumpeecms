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
namespace frontend\models;

/**
 * Description of Gallery
 *
 * @author Peter
 */
use Yii;
class Feedback extends \yii\db\ActiveRecord{
    //put your code here
    public function behaviors(){
        return[
            'frontend\components\FormSubmitBehavior',
            'frontend\components\FormSubmitHookBehaviour',
        ];
    }
    public static function tableName()
    {
        return 'tbl_feedback';
    }
    
    public function getDetails(){        
        return $this->hasMany(FeedbackDetails::className(),['feedback_id'=>'id']);
    }
    public function getOwner(){
        return $this->hasOne(Users::className(),['username'=>'usrname']);
    }
    public function getArticle(){
        return $this->hasOne(Articles::className(),['id'=>'target_id','feedback_type'=>'article']);
    }
    public function getSubmitForm(){
        return $this->hasOne(FormSubmit::className(),['id'=>'target_id']);
    }
    public function getFile(){
        return $this->hasMany(FeedbackFiles::className(),['feedback_id'=>'id']);
    }
    
}
