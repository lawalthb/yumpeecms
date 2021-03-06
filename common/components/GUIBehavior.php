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
namespace common\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;
use yii\db\Expression;
use Yii;
use frontend\models\ClassSetup;
use backend\models\ClassElement;
use backend\models\ClassAttributes;
use frontend\models\Blocks;
use backend\models\BlockGroup;
use backend\models\BlockGroupList;
use backend\models\Templates;
use frontend\models\FormData;
use frontend\models\FormSubmit;
use backend\models\Forms;
use frontend\models\FormFiles;
use backend\models\Roles;
use frontend\components\ContentBuilder;
use frontend\models\Twig;
use frontend\models\Users;
use frontend\models\ProfileDetails;
use frontend\models\Themes;
use frontend\models\Media;

class GUIBehavior extends Behavior
{
    
   public $fields;
   public $gui_type; //this can be select , checkbox, radio buttons

    public function events()
    {
        return [
            // after find event
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',            
        ];
    }
    
    public function afterFind(){
        //we define the shortcodes here and then process when a match is found in this function
        $pattern = "/{yumpee_class}(.*?){\/yumpee_class}/";        
        $pattern_data = "/{yumpee_data}(.*?){\/yumpee_data}/";
        $pattern_user = "/{yumpee_user}(.*?){\/yumpee_user}/";
        $pattern_login_to_view="/{yumpee_login_to_view}(.*?){\/yumpee_login_to_view}/";
        $pattern_hide_on_login="/{yumpee_hide_on_login}(.*?){\/yumpee_hide_on_login}/";
        $pattern_setting= "/{yumpee_setting}(.*?){\/yumpee_setting}/";
        $pattern_setting_extra= "/{yumpee_setting:(.*?)}(.*?){\/yumpee_setting}/";
        $pattern_twig= "/{yumpee_include}(.*?){\/yumpee_include}/";
        $pattern_get= "/{yumpee_get}(.*?){\/yumpee_get}/";
        $pattern_post= "/{yumpee_post}(.*?){\/yumpee_post}/";
        $pattern_block= "/{yumpee_block}(.*?){\/yumpee_block}/";
        $pattern_block_group= "/{yumpee_block_group}(.*?){\/yumpee_block_group}/";
        $pattern_widget="/{yumpee_widget}(.*?){\/yumpee_widget}/";
        $pattern_backend="/{yumpee_backend_view}(.*?){\/yumpee_backend_view}/";
        $pattern_map="/{yumpee_map}(.*?){\/yumpee_map}/";
        $pattern_role= "/{yumpee_role:(.*?)}(.*?){\/yumpee_role}/";
        $pattern_env = "/{yumpee_env}(.*?){\/yumpee_env}/";
        $pattern_menu = "/{yumpee_menu}(.*?){\/yumpee_menu}/";
        $pattern_translate_full= "/{yumpee_t:(.*?)}(.*?){\/yumpee_t}/";
        $pattern_translate= "/{yumpee_t}(.*?){\/yumpee_t}/";
        $pattern_submit="/{yumpee_submit}(.*?){\/yumpee_submit}/";
        $pattern_testimonial="/{yumpee_testimonial}(.*?){\/yumpee_testimonial}/";
        $pattern_article="/{yumpee_article}(.*?){\/yumpee_article}/";
        $pattern_page="/{yumpee_page}(.*?){\/yumpee_page}/";
        $pattern_comment="/{yumpee_comment}(.*?){\/yumpee_comment}/";
        $pattern_gallery="/{yumpee_gallery}(.*?){\/yumpee_gallery}/";
        $pattern_media="/{yumpee_media}(.*?){\/yumpee_media}/";
        $pattern_download="/{yumpee_download}(.*?){\/yumpee_download}/";
        
		
		$themes = new Themes();
		$theme_id=$themes->dataTheme;
		
		if($theme_id=="0"):
			$theme_id = ContentBuilder::getSetting("current_theme");			
		endif;
							
        foreach($this->owner->fields as $field):
        $content = $this->owner->{$field};  
        //$content = str_replace("\r\n","",$content);
        $content = preg_replace_callback($pattern_setting,function ($matches) use($theme_id){                            
                            $replacer = ContentBuilder::getSetting($matches[1],$theme_id);                            
                            return $replacer;
                    },$content); 
        $content = preg_replace_callback($pattern_get,function ($matches) {
                            $replacer="";
                            $replacer=Yii::$app->request->get($matches[1]);
                            return $replacer;
                    },$content); 
                  
        $content = preg_replace_callback($pattern_post,function ($matches) {
                            $replacer="";
                            $replacer=Yii::$app->request->post($matches[1]);
                            return $replacer;
                    },$content);         
        $content = preg_replace_callback($pattern_translate_full,function ($matches) {
                            $replacer="";
                            if($matches[2]<>"0"):
                                //this means we are setting the default language
                                \Yii::$app->language = $matches[2]; 
                            endif;
                            $replacer=\Yii::t('app',$app->request->get($matches[1]));
                            return $replacer;
                    },$content); 
        $content = preg_replace_callback($pattern_translate,function ($matches) {
                            $replacer="";                            
                            $replacer=\Yii::t('app',$app->request->get($matches[1]));
                            return $replacer;
                    },$content);
        $content = preg_replace_callback($pattern_env,function ($matches) {
                            $replacer=null;
                            if($matches[1]=="pathInfo"):
                                $replacer=Yii::$app->request->pathInfo;
                                return $replacer;
                            endif;
                            if($matches[1]=="url"):
                                $replacer=ContentBuilder::getActionURL(Yii::$app->request->getAbsoluteUrl());
                                return $replacer;
                            endif;
                            if($matches[1]=="username" && Yii::$app->user->identity!=null):
                                $replacer=Yii::$app->user->identity->username;
                                return $replacer;
                            endif;
                            if($matches[1]=="role_id" && Yii::$app->user->identity!=null):
                                $replacer=Yii::$app->user->identity->role_id;
                                return $replacer;
                            endif;                            
                            if(isset($_SERVER[$matches[1]])):
                                $a = $matches[1];
                                $replacer = $_SERVER[$a];
                            endif;
                            return $replacer;
                    },$content);  
        $content = preg_replace_callback($pattern,function ($matches) {
                            $replacer="";
                            $elements=[];
                            list($name,$attribute,$id) = preg_split("/:/",preg_replace("/}/",":",$matches[1]));
                            $class_setup = ClassSetup::find()->where(['name'=>$name])->one();
                            if(trim($attribute=="child")):
                                if($id=="*"):
                                        $elements = ClassSetup::find()->with('displayImage','parent','child','list')->asArray()->where(['parent_id'=>$class_setup->id])->orderBy(['display_order'=>SORT_ASC,'alias'=>SORT_ASC])->all();
                                    else:
                                        $elements = ClassSetup::find()->with('displayImage','parent','child','list')->asArray()->where(['name'=>$id])->orderBy(['display_order'=>'SORT_ASC'])->one();
                                endif;
                                
                            endif;
                            if(trim($attribute)=="list"||trim($attribute)=="elements"):   
                                if($id=="*"):
                                    $elements = ClassElement::find()->with('displayImage','parent','child')->asArray()->where(['class_id'=>$class_setup['id']])->orderBy(['display_order'=>SORT_ASC,'alias'=>SORT_ASC])->all();
                                elseif($id=="parent"):
                                    $elements = ClassElement::find()->with('displayImage','parent','child')->asArray()->where(['class_id'=>$class_setup['id']])->andWhere("parent_id=''")->orderBy(['display_order'=>SORT_ASC,'alias'=>SORT_ASC])->all();
                                else:
                                    $elements = ClassElement::find()->with('displayImage','parent','child')->asArray()->where(['class_id'=>$class_setup['id']])->andWhere("name='".$id."'")->orderBy('alias')->one();
                                endif;
                            endif;
                            if(trim($attribute)=="property"):   
                                if($id=="*"):
                                    $elements = ClassAttributes::find()->where(['class_id'=>$class_setup['id']])->orderBy(['display_order'=>SORT_ASC,'alias'=>SORT_ASC])->all();
                                else:
                                    $elements = ClassAttributes::find()->where(['class_id'=>$class_setup['id']])->andWhere("name='".$id."'")->orderBy('alias')->one();
                                endif;
                            endif;
							if($elements!=null){
								$replacer = \yii\helpers\Json::encode($elements);
							}
                            return $replacer;
                    },$content);
                    
        $content = preg_replace_callback($pattern_data,function ($matches) {
                            $replacer="";
                            $order="";
                            $limit="";
							$relations="";
                            $params=null;
                            $data_query=[];
                            $sent_data = explode(":",$matches[1]);
                            if($sent_data[0]!=null):
                                $name = $sent_data[0];
                            endif;
                            if(count($sent_data) > 1):
                                $params = $sent_data[1];
                            endif;
                            if(count($sent_data) > 2):
                                $order = $sent_data[2];
                            endif;
                            if(count($sent_data) > 3):
                                $limit = $sent_data[3];
                            endif;
							if(count($sent_data) > 4):
								$relations=$sent_data[4];
							endif;
							
                            //list($name,$params) = explode(":",$matches[1]);
                            $form = Forms::find()->select('id')->where(['name'=>$name])->one();
                            //we handle filtering of data search parameters
							$search_succeed=0;
                            
                            $andWhere="";
							$search_params_pipe=explode("|",$params);
							$search_params_amp=explode("&",$params);
							
							if((sizeof($search_params_pipe)==1)&& (sizeof($search_params_amp)==1) && $params!=null):
										$data_query = FormData::find()->select('form_submit_id');
										$counter=0;
										foreach($search_params_pipe as $param):
                                        list($p,$v)=explode("=",$param);                
                                        //this is used to search based on submit id
                                        if($p=="form_submit_id"):
                                            $data_query->orWhere('form_submit_id="'.$v.'"');
											$search_succeed=1;
                                            continue;
                                        endif;
                                        if((trim($p)=="url") && (trim($v)!="")):
                                            $andWhere="url='".$v."'";
											$search_succeed=1;
                                            continue;
                                        endif;
                                        if((trim($p)=="usrname") && (trim($v)!="")):
                                            $andWhere="usrname='".$v."'";
											$search_succeed=1;
                                            continue;
                                        endif;
                
                                        if(count($search_params_pipe)==1):
                                            $data_query->andWhere('param="'.$p.'"')->orWhere(['like','param_val',$v])->andFilterCompare('param_val',$v);
                                        else:                    
                                            $data_query->andWhere('param="'.$p.'"')->orWhere(['like','param_val',$v]);                    
                                        endif;
										$search_succeed=1;
										endforeach;
                                    
                               endif;
								
                            if(($params!=null) && ($search_succeed==0)):
                                $data_query = FormData::find()->select('form_submit_id');
                                $search_params=explode("|",$params);
                                $counter=0;
                                $search_succeed=0;
                                if(sizeof($search_params) > 1):
                                foreach($search_params as $param):
                                    list($p,$v)=explode("=",$param);
                                    if(trim($p)=="url"):
                                        $andWhere="url='".$v."'";
                                        continue;
                                    endif;
                                    if(trim($p)=="usrname"):
                                        $andWhere="usrname='".$v."'";
                                        continue;
                                    endif;
                                    if($counter==0):
                                        $data_query->andWhere('param="'.$p.'"')->andFilterCompare('param_val',$v);
                                    else:
                                        $data_query->andWhere('param="'.$p.'"')->orWhere(['like','param_val',$v])->andFilterCompare('param_val',$v);
                                    endif;
                                    $counter++;
                                endforeach;
                                $search_succeed=1;
                                endif;
                                if($search_succeed < 1):
                                    $search_params=explode("&",$params);
                                    if(sizeof($search_params) > 1):
                                        $form_arr= array();
                                        $int_count=0;
                                        foreach($search_params as $param):                    
                                            $pn = explode("=",$param);
                                            if(count($pn) > 1):
                                                list($p,$v)=explode("=",$param);
                                            endif;
                                            $pl=explode("<",$param);
                                            $pg=explode(">",$param);
                                            $plq=explode("<=",$param);
                                            $pgq=explode(">=",$param);
                                            //this is used to search based on submit id
                                            if($p=="form_submit_id"):
                                                $data_query->andWhere('form_submit_id="'.$v.'"');
                                                continue;
                                            endif;
                                            if(count($pgq) > 1):
                                                $form_arr[$int_count] = FormData::find()->select('form_submit_id')->where(['param'=>$pgq[0]])->andWhere('param_val >="'.$pgq[1].'"')->column();
                                                $int_count++;
                                                continue;
                                            endif;
                                            if(count($plq) > 1):
                                                $form_arr[$int_count] = FormData::find()->select('form_submit_id')->where(['param'=>$plq[0]])->andWhere('param_val <="'.$plq[1].'"')->column();
                                                $int_count++;
                                                continue;
                                            endif;
                                            if(count($pg) > 1):
                                                $form_arr[$int_count] = FormData::find()->select('form_submit_id')->where(['param'=>$pg[0]])->andWhere('param_val >"'.$pg[1].'"')->column();
                                                $int_count++;
                                                continue;
                                            endif;
                                            if(count($pl) > 1):
                                                $form_arr[$int_count] = FormData::find()->select('form_submit_id')->where(['param'=>$pl[0]])->andWhere('param_val <"'.$pl[1].'"')->column();
                                                $int_count++;
                                                continue;
                                            endif;
                                            $form_arr[$int_count] = FormData::find()->select('form_submit_id')->where(['param'=>$p])->andWhere(['like','param_val',$v])->column();
                                            $int_count++;
                                        endforeach;
                                        $form_submit_arr = $form_arr[0];
                                        foreach ($form_arr as $form_submit_item):
                                            $form_submit_arr = array_intersect($form_submit_arr,$form_submit_item);
                                        endforeach;
                                        $data_query->where(['IN','form_submit_id',$form_submit_arr]);
                                        $search_succeed=1;
                                    endif;            
                                endif;
                                
                                
                                $data_query->all();
                            endif;
                            $submit_query = FormSubmit::find();
                            $order_sorted=0;
                            if($order!=""):
                                if($order=="random"):
                                    $submit_query->orderBy(new Expression('rand()'));
                                    $order_sorted=1;
                                endif;
                                if($order=="last"):
                                    $submit_query->orderBy(['date_stamp'=>SORT_DESC]);
                                    $order_sorted=1;
                                endif;
                                if($order=="first"):
                                    $submit_query->orderBy(['date_stamp'=>SORT_ASC]);
                                    $order_sorted=1;
                                endif;
                                if($order=="views"):
                                    $submit_query->orderBy(['no_of_views'=>SORT_DESC]);
                                    $order_sorted=1;
                                endif;
                                if($order=="user"):
                                    $submit_query->orderBy(['usrname'=>SORT_ASC]);
                                    $order_sorted=1;
                                endif;
                                if($order=="rating"):
                                    $submit_query->orderBy(['rating'=>SORT_DESC]);
                                    $order_sorted=1;
                                endif;                               
                                
                                if($order=="count"):
                                    if($andWhere!=""):
                                        $elements = $submit_query->where(['IN','id',$data_query])->andWhere('form_id="'.$form->id.'"')->andWhere($andWhere);
                                    else:
                                        $elements = $submit_query->where(['IN','id',$data_query])->andWhere('form_id="'.$form->id.'"');
                                    endif;
                                    $elements = $submit_query->count();
                                    $replacer = \yii\helpers\Json::encode($elements);
                                    return $replacer;
                                endif;
                                if($order_sorted==0):
                                    $order_arr= explode(" ",$order);
                                    $ordering="";
                                    if(sizeof($order_arr) > 1):
                                        $ordering = $order_arr[1];
                                        $order=trim($order_arr[0]);
                                    endif;
                                    if(trim($ordering)=="DESC"):
                                        if($limit!=""):
                                            $submit_arr = FormData::find()->select('form_submit_id')->where(['param'=>$order])->orderBy(['param_val'=>SORT_DESC])->offset($limit)->asArray()->column();
                                        else:
                                            $submit_arr = FormData::find()->select('form_submit_id')->where(['param'=>$order])->orderBy(['param_val'=>SORT_DESC])->asArray()->column();
                                        endif;                                        
                                    else:
                                        if($limit!=""):
                                            $submit_arr = FormData::find()->select('form_submit_id')->where(['param'=>$order])->orderBy(['param_val'=>SORT_ASC])->offset($limit)->asArray()->column();
                                        else:
                                            $submit_arr = FormData::find()->select('form_submit_id')->where(['param'=>$order])->orderBy(['param_val'=>SORT_ASC])->asArray()->column();  
                                        endif;
                                        
                                    endif;
                                    $submit_query->andWhere(['in','id',$submit_arr])->orderBy(new Expression('FIND_IN_SET (id,:form_submit_id)'))->addParams([':form_submit_id'=>implode(",",$submit_arr)]);
                                    
                                endif;
                            endif;
                            if($limit!=""):
                                $submit_query->limit($limit);
                            endif;
							
                            if($andWhere!=""){
								if($relations=="basic"):
										$elements = $submit_query->with('data','user','user.displayImage')->asArray()->where(['IN','id',$data_query])->andWhere('form_id="'.$form->id.'"')->andWhere($andWhere)->all();
                                                                elseif($relations=="file"):
                                                                                $elements = $submit_query->with('data','user','user.displayImage','file')->asArray()->where(['IN','id',$data_query])->andWhere('form_id="'.$form->id.'"')->andWhere($andWhere)->all();
								else:
										$elements = $submit_query->with('data','file','user','user.displayImage','data.setup','data.setupVal','data.element','data.elementVal','data.property','data.propertyVal')->asArray()->where(['IN','id',$data_query])->andWhere('form_id="'.$form->id.'"')->andWhere($andWhere)->all();
								endif;
							}else{
								if($relations=="basic"):
										$elements = $submit_query->with('data','user','user.displayImage')->asArray()->where(['IN','id',$data_query])->andWhere('form_id="'.$form->id.'"')->all();
                                                                elseif($relations=="file"):
                                                                                $elements = $submit_query->with('data','user','user.displayImage','file')->asArray()->where(['IN','id',$data_query])->andWhere('form_id="'.$form->id.'"')->andWhere($andWhere)->all();
								else:
										$elements = $submit_query->with('data','file','user','user.displayImage','data.setup','data.setupVal','data.element','data.elementVal','data.property','data.propertyVal')->asArray()->where(['IN','id',$data_query])->andWhere('form_id="'.$form->id.'"')->all();
								endif;
                            }
                            $replacer = \yii\helpers\Json::encode($elements);
                            return $replacer;
                    },$content); 
                    
        $content = preg_replace_callback($pattern_user,function ($matches) {
                            $replacer="";
                            $order="";
                            $limit="";
                            $params=null;
                            $data_query=[];
                            $sent_data = explode(":",$matches[1]);
                            $search_succeed=0;
                            $submit_query = Users::find();
                            if($sent_data[0]!=null):
                                $params = $sent_data[0];
                            endif;
                            if(isset($sent_data[1])):
                                $order = $sent_data[1];
                            endif;
                            if(isset($sent_data[2])):
                                $limit = $sent_data[2];
                            endif;                            
                            
                            $andWhere="";                            
                            if($params!=null):
                                $data_query = ProfileDetails::find()->select('profile_id');
                                $search_params=explode("|",$params);
                                $counter=0;
                                $search_criteria=array('id','username','first_name','last_name','title','role_id','email','status','created_at','updated_at');
                                if(sizeof($search_params)==1):
                                    
                                    list($p,$v)=explode("=",$search_params[0]);
                                    if(in_array($p,$search_criteria)):  
                                        //echo $v;
                                        //exit;
                                        $submit_query->where(['$p=>$v']);
                                        $search_succeed=1;
                                        $counter++;                                        
                                    endif;
                                endif;
                                
                                if(sizeof($search_params) > 1 ):
                                    
                                foreach($search_params as $param):
                                    list($p,$v)=explode("=",$param);
                                    if(in_array($p,$search_criteria)):  
                                        $submit_query->andWhere($p.'="'.$v.'"')->orWhere(['like',$p,$v]);
                                        $search_succeed=1;
                                        $counter++;
                                        continue;
                                    endif;
                                    if($counter==0):
                                        $data_query->andWhere('param="'.$p.'"')->andFilterCompare('param_val',$v);
                                    else:
                                        $data_query->andWhere('param="'.$p.'"')->orWhere(['like','param_val',$v])->andFilterCompare('param_val',$v);
                                    endif;
                                    $counter++;
                                endforeach;
                                $data_query->all();
                                $search_succeed=1;
                                endif;
                                
                                if($search_succeed < 1):
                                    
                                    $search_params=explode("&",$params);
                                    $search_criteria=array('id','username','first_name','last_name','title','role_id','email','status','created_at','updated_at');
                                    if(sizeof($search_params) > 1):
                                        $form_arr= array();
                                        $int_count=0;
                                        foreach($search_params as $param):
                                            list($p,$v)=explode("=",$param);
                                            if(in_array($p,$search_criteria)):
                                            //we add this to the submit query condition later on                                                
                                                $andWhere=$p."='".$v."'";    
                                                continue;
                                            endif;
                                            $pn = explode("=",$param);
                                            if(count($pn) > 1):
                                                list($p,$v)=explode("=",$param);
                                            endif;
                                            $pl=explode("<",$param);
                                            $pg=explode(">",$param);
                                            $plq=explode("<=",$param);
                                            $pgq=explode(">=",$param);
                                            //this is used to search based on submit id
                                            if($p=="profile_id"):
                                                $data_query->andWhere('profile_id="'.$v.'"');
                                                continue;
                                            endif;
                                            if(count($pgq) > 1):
                                                $form_arr[$int_count] = ProfileDetails::find()->select('profile_id')->where(['param'=>$pgq[0]])->andWhere('param_val >="'.$pgq[1].'"')->column();
                                                $int_count++;
                                                continue;
                                            endif;
                                            if(count($plq) > 1):
                                                $form_arr[$int_count] = ProfileDetails::find()->select('profile_id')->where(['param'=>$plq[0]])->andWhere('param_val <="'.$plq[1].'"')->column();
                                                $int_count++;
                                                continue;
                                            endif;
                                            if(count($pg) > 1):
                                                $form_arr[$int_count] = ProfileDetails::find()->select('profile_id')->where(['param'=>$pg[0]])->andWhere('param_val >"'.$pg[1].'"')->column();
                                                $int_count++;
                                                continue;
                                            endif;
                                            if(count($pl) > 1):
                                                $form_arr[$int_count] = ProfileDetails::find()->select('profile_id')->where(['param'=>$pl[0]])->andWhere('param_val <"'.$pl[1].'"')->column();
                                                $int_count++;
                                                continue;
                                            endif;
                                            $form_arr[$int_count] = ProfileDetails::find()->select('profile_id')->where(['param'=>$p])->andWhere(['like','param_val',$v])->column();
                                            $int_count++;
                                        endforeach;
                                        $form_submit_arr = $form_arr[0];
                                        foreach ($form_arr as $form_submit_item):
                                            $form_submit_arr = array_intersect($form_submit_arr,$form_submit_item);
                                        endforeach;
                                        $data_query->where(['IN','profile_id',$form_submit_arr]);
                                        $search_succeed=1;
                                    endif;
                                endif;
                                if($search_succeed < 1):                                    
                                    $search_criteria=array('id','username','first_name','last_name','title','role_id','email','status','created_at','updated_at');
                                    foreach($search_params as $param):
                                    list($p,$v)=explode("=",$param);
                                    if(in_array($p,$search_criteria)):
                                        
                                        //we add this to the submit query condition later on
                                        $andWhere=$p."='".$v."'";    
                                        continue;
                                    endif;
                                    if($counter==0):
                                        $data_query->andWhere('param="'.$p.'"')->andFilterCompare('param_val',$v);
                                    else:
                                        $data_query->andWhere('param="'.$p.'"')->orWhere(['like','param_val',$v])->andFilterCompare('param_val',$v);
                                    endif;
                                    $counter++;
                                    endforeach;
                                    $data_query->all();
                                endif;
                            endif;
                            
                            
                            $order_sorted=0;
                            if($order!=""):
                                $order_arr= explode(" ",$order);
                                $ordering="";
                                $order_sorted=0;
                                if(sizeof($order_arr) > 1):
                                    $ordering = $order_arr[1];
                                    $order=$order_arr[0];
                                endif;
                                if($order=="random"):
                                    $submit_query->orderBy(new Expression('rand()'));
                                    $order_sorted=1;
                                endif;
                                if($order=="last"):
                                    $submit_query->orderBy(['created_at'=>SORT_DESC]);
                                    $order_sorted=1;
                                endif;
                                if($order=="first"):
                                    $submit_query->orderBy(['created_at'=>SORT_ASC]);
                                    $order_sorted=1;
                                endif;
                                if($order=="views"):
                                    $submit_query->orderBy(['no_of_views'=>SORT_DESC]);
                                    $order_sorted=1;
                                endif;
                                if($order=="user"):
                                    $submit_query->orderBy(['username'=>SORT_ASC]);
                                    $order_sorted=1;
                                endif;
                                if($order=="count"):
                                    if($andWhere!=""):
                                        $elements = $submit_query->where(['IN','id',$data_query])->andWhere($andWhere);
                                    else:
                                        if(!empty($data_query)):
                                            $elements = $submit_query->where(['IN','id',$data_query]);
                                        endif;
                                    endif;
                                    $elements = $submit_query->count();
                                    $replacer = \yii\helpers\Json::encode($elements);
                                    return $replacer;
                                endif;
                                if($order_sorted==0): 
                                   if(trim($ordering)=="DESC"):
                                        $submit_query->orderBy([$order=>SORT_DESC]);
                                    else:
                                        $submit_query->orderBy([$order=>SORT_ASC]);
                                    endif; 
                                endif;
                            endif;
                            if($limit!=""):
                                $submit_query->limit($limit);
                            endif;
							
                            if($andWhere!=""){
                                    if(!empty($data_query)):
                                        $elements = $submit_query->with('details','profileFiles')->asArray()->where(['IN','id',$data_query])->andWhere($andWhere)->all();
                                    else:
                                        $elements = $submit_query->with('details','profileFiles')->asArray()->andWhere($andWhere)->all();
                                    endif;
				}else{
                                    if(!empty($data_query)):
                                            $elements = $submit_query->with('details','profileFiles')->asArray()->where(['IN','id',$data_query])->all();
                                    else:
                                            $elements = $submit_query->with('details','profileFiles')->asArray()->all();
                                    endif;
                            }
                            $replacer = \yii\helpers\Json::encode($elements);
                            return $replacer;
                    },$content);
                    
        $content = preg_replace_callback($pattern_testimonial,function ($matches) {
                            $replacer="";
                            if($matches[1]<>"all"):
                                $testimonial_arr=\backend\models\Testimonials::find()->limit($matches[1])->all();
                            else:
                                $testimonial_arr=\backend\models\Testimonials::find()->all();
                            endif;
                            
                            return \yii\helpers\Json::encode($testimonial_arr);
                    },$content);
        $content = preg_replace_callback($pattern_comment,function ($matches) {
                            $replacer="";
                            if($matches[1]<>"all"):
                                $testimonial_arr=\backend\models\Comments::find()->limit($matches[1])->all();
                            else:
                                $testimonial_arr=\backend\models\Comments::find()->all();
                            endif;
                            
                            return \yii\helpers\Json::encode($testimonial_arr);
                    },$content);
        $content = preg_replace_callback($pattern_gallery,function ($matches) {
                            $replacer="";
                            $gallery_arr=[];
                            if($matches[1]<>"all"):
                                $gallery_arr=\backend\models\Gallery::find()->with('items')->asArray()->where(['name'=>$matches[1]])->one();                            
                            endif;
                            
                            return \yii\helpers\Json::encode($gallery_arr);
                    },$content);
        $content = preg_replace_callback($pattern_media,function ($matches) {
                            $replacer="";
                            $media_arr=[];
                            if($matches[1]<>"all"):
                                $media_arr=Media::find()->with('publisher')->asArray()->where(['id'=>$matches[1]])->one();    
                            else:
                                $media_arr=Media::find()->with('publisher')->asArray()->all();
                            endif;
                            
                            return \yii\helpers\Json::encode($media_arr);
                    },$content);
        //we process downloads
        $content = preg_replace_callback($pattern_download,function ($matches) {
                            //download single file
                            //download a group of files by their id and set their archive format
                            //download file as - download as file name
                            
                            $replacer="";
                            $sent_data = explode(":",$matches[1]);
                            $media_arr=[];
                            if($sent_data[0]<>"all"):
                                $media_arr=FormFiles::find()->where(['id'=>$sent_data[0]])->one();  
                            endif;
                            $download_name=basename(Yii::getAlias('@uploads/uploads/').$media_arr["file_path"]);
                            if($sent_data[1]!=null):
                                $download_name=$sent_data[1];
                            endif;
                            header('Content-Description: File Transfer');
                            header('Content-Type: '.$media_arr["file_type"]);
                            header('Content-Disposition: attachment; filename='.$download_name);
                            header('Content-Transfer-Encoding: binary');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate');
                            header('Pragma: public');
                            header('Content-Length: ' . filesize(Yii::getAlias('@uploads/uploads/').$media_arr["file_path"]));
                            ob_clean();
                            flush();
                            readfile(Yii::getAlias('@uploads/uploads/').$media_arr["file_path"]);
                            exit;
                            
                            return \yii\helpers\Json::encode($media_arr);
                    },$content);
        //process page calls
        $content = preg_replace_callback($pattern_page,function ($matches) { 
            if($matches[1]=="*"):
                $page_arr = \frontend\models\Pages::find()->all();
            else:
                if (strpos($matches[1], ':') !== false) :
                $params = explode(":",$matches[1]);
                
                if($params[0]!=null):
                    $submit_query = \frontend\models\Pages::find();
                     $filter = explode("|",$params[0]);
                                foreach ($filter as $filter_rec):
                                    list($v,$p)=explode("=",$filter_rec);
                                    if($v=="route"):
                                        $template = \backend\models\Templates::find()->where(['route'=>$p])->one();
                                        $submit_query->where(['template'=>$template['id']]);
                                    else:
					$submit_query->andWhere($v.'='.$p);
                                    endif;                                      
                                endforeach;
                                if($params[1]!=null):
                                    $limit = $params[1];
                                    $submit_query->limit($limit);
                                endif;
                                if($params[2]!=null):
                                    $p=explode(" ",$params[2]);
                                    if(count($p) > 1):
                                        if($p[1]=="DESC"):
                                            $sort="SORT_DESC";
                                        else:
                                            $sort="SORT_ASC";
                                        endif;
                                    else:
                                        $sort="SORT_ASC";
                                    endif;
                                    $orderby=$p[0];
                                    if($params[2]=="last"):                                    
                                        $submit_query->orderBy(['updated'=>SORT_DESC]);
                                    elseif($params[2]=="first"):
                                        $submit_query->orderBy(['updated'=>SORT_ASC]);
                                    else:
                                        if($sort=="SORT_DESC"):
                                            $submit_query->orderBy([$orderby=>SORT_DESC]);
                                        else:
                                            $submit_query->orderBy([$orderby=>SORT_ASC]);
                                        endif;
                                    endif;
                                endif;
                                $page_arr = $submit_query->all();
                                return \yii\helpers\Json::encode($page_arr);
                endif;
                endif;
                $page_arr = \frontend\models\Pages::find()->where(['url'=>$matches[1]])->one();
            endif;
            return \yii\helpers\Json::encode($page_arr);
        },$content);
        //process Articles call
        $content = preg_replace_callback($pattern_article,function ($matches) {                            
                            $limit=3;
                            $submit_query = \backend\models\Articles::find();
                            $params = explode(":",$matches[1]);
                            if($params[0]!=null):
                                if($params[0]=="*"):
								
				else:
                                $filter = explode("|",$params[0]);
                                foreach ($filter as $filter_rec):
                                    list($v,$p)=explode("=",$filter_rec);
                                    if($v=="index"):                                        
                                        $page = \backend\models\Pages::find()->where(['url'=>$p])->one();
                                        $blog_index_articles = \backend\models\ArticlesBlogIndex::find()->select('articles_id')->where(['blog_index_id'=>$page['id']])->column();                                        
                                        $submit_query->where(['IN','id',$blog_index_articles]);
                                    endif;
                                    if($v=="category"):
                                        $page = \backend\models\ArticlesCategories::find()->where(['url'=>$p])->one();
                                        $blog_index_articles = \backend\models\ArticlesCategoryRelated::find()->select('articles_id')->where(['category_id'=>$page['id']])->column();
                                        $submit_query->where(['IN','id',$blog_index_articles]);
                                    endif;
                                endforeach;
                                endif;
                            endif;
                            if($params[1]!=null):
                                $limit = $params[1];
                                $submit_query->limit($limit);
                            endif;
                            if($params[2]!=null):
                                $p=explode(" ",$params[2]);
                                if(count($p) > 1):
                                    if($p[1]=="DESC"):
                                        $sort="SORT_DESC";
                                    else:
                                        $sort="SORT_ASC";
                                    endif;
                                else:
                                        $sort="SORT_ASC";
                                endif;
                                $orderby=$p[0];
                                if($params[2]=="last"):                                    
                                    $submit_query->orderBy(['date'=>SORT_DESC]);
                                elseif($params[2]=="first"):
                                    $submit_query->orderBy(['date'=>SORT_ASC]);
                                else:
                                    if($sort=="SORT_DESC"):
                                        $submit_query->orderBy([$orderby=>SORT_DESC]);
                                    else:
                                        $submit_query->orderBy([$orderby=>SORT_ASC]);
                                    endif;
                                endif;
                            endif;
                            $article_arr = $submit_query->with('documents','feedback','details','approvedComments','displayImage','blogIndex','blogIndex.page','author')->asArray()->all();                            
                            return \yii\helpers\Json::encode($article_arr);
                    },$content);
        //End of Articles shortcodes
                    
        $content = preg_replace_callback($pattern_menu,function ($matches) {
                            $replacer="";
                            $menu_arr=\backend\models\MenuProfile::find()->where(['name'=>$matches[1]])->one();
                            if($menu_arr==null):
                                $menu_id=0;
                            else:
                                $menu_id = $menu_arr['id'];
                            endif;
                            
                            return \yii\helpers\Json::encode(\backend\models\Menus::getProfileMenus($menu_id));
                    },$content);
        $content = preg_replace_callback($pattern_login_to_view,function ($matches) {
                            $replacer="";
                            if(Yii::$app->user->id):
                                //list($replacer) = preg_split("/:/",preg_replace("/}/",":",$matches[1]));
                                return $matches[1]; 
                            else:                                
                                $replacer="";
                            endif;
                            return $replacer;
                    },$content);  
        $content = preg_replace_callback($pattern_hide_on_login,function ($matches) {
                            $replacer="";
                            if(Yii::$app->user->isGuest):
                                //list($replacer) = preg_split("/:/",preg_replace("/}}/",":",$matches[1]));
                                return $matches[1]; 
                            else:
                                $replacer="";
                            endif;
                            
                            return $replacer;
                    },$content);  
        $content = preg_replace_callback($pattern_role,function ($matches) {
                            if($matches[1]=="all"):
                                $replacer = Roles::find()->orderBy('name')->all(); 
                                return \yii\helpers\Json::encode($replacer);
                            endif;
                        if(Yii::$app->user->id):
                            
                            $role = Roles::find()->where(['id'=>Yii::$app->user->identity->role_id])->one();
                            if($role->name==$matches[1]):
                                return $matches[2];
                            else:
                                return "";
                            endif;
                        endif;
                    },$content);
        $array=$content;           
        $array = preg_replace_callback($pattern_setting,function ($matches) use($theme_id){
                            if($matches[1]=="yumpee_role_home_page"):
                                return ContentBuilder::getRoleHomePage();
                            endif;
                            if($matches[1]=="*" || $matches[1]=="~*"):
                                return \yii\helpers\Json::encode(ContentBuilder::getSetting($matches[1],$theme_id));
                            endif;
                            $replacer = ContentBuilder::getSetting($matches[1],$theme_id);                            
                            return $replacer;
                    },$array); 
        $array = preg_replace_callback($pattern_setting_extra,function ($matches) {
                            if($matches[2]=="yumpee_role_home_page"):
                                return ContentBuilder::getRoleHomePage();
                            endif;
                            if($matches[2]=="*" || $matches[2]=="~*"):
                                return \yii\helpers\Json::encode(ContentBuilder::getSetting($matches[2],$theme_id));
                            endif;
                            $replacer = ContentBuilder::getSetting($matches[2]);  
                            if($replacer==""||$replacer==null):
				return $matches[1];
                            endif;
                            return $replacer;
                    },$array); 	
        $array = preg_replace_callback($pattern_widget,function ($matches) {
                            $replacer = "<div class=\"yumpee_custom_widget:".$matches[1]."\"></div>";                            
                            return $replacer;
                    },$array);
        $array = preg_replace_callback($pattern_backend,function ($matches) {
                            return "";
                    },$array);
        $array = preg_replace_callback($pattern_block,function ($matches) {
                            $replacer = Blocks::find()->where(['name'=>$matches[1]])->one();                            
                            if($replacer<>null):
                                return \yii\helpers\Json::encode($replacer);
                            else:
                                return \yii\helpers\Json::encode(['']);
                            endif;
                    },$array); 
        $array = preg_replace_callback($pattern_block_group,function ($matches) {
                            list($name,$style,$limit) = explode(":",$matches[1]);
                            $block_group_id = BlockGroup::find()->where(['name'=>$name])->one();
                            if($block_group_id<>null):
                                $block_array = BlockGroupList::find()->select('block_id')->where(['group_id'=>$block_group_id->id])->column();
                                $replacer = Blocks::find()->where(['IN','id',$block_array])->orderBy(['name'=>SORT_ASC])->limit($limit)->all();
                                if($style=="random"):
                                    $replacer = Blocks::find()->where(['IN','id',$block_array])->orderBy(new Expression('rand()'))->limit($limit)->all();                            
                                endif;
                                return \yii\helpers\Json::encode($replacer);
                            else:
                                return \yii\helpers\Json::encode(['']);
                            endif;
                    },$array); 
                    
        $array = preg_replace_callback($pattern_twig,function ($matches) use($theme_id){
                            $loader = new Twig();
                            $twig = new \Twig_Environment($loader); 
                            $metadata['saveURL'] = \Yii::$app->getUrlManager()->createUrl('ajaxform/save');
                            $metadata['param'] = Yii::$app->request->csrfParam;
                            $metadata['token'] = Yii::$app->request->csrfToken;    
                            $params = explode(":",$matches[1]);        
                            
                            if(count($params) > 1):
                                    $a = Twig::find()->where(['renderer'=>$params[0],'theme_id'=>$theme_id])->one();
                                    parse_str($params[1], $output);
                                    $content = $twig->render($a->filename,['app'=>Yii::$app,'metadata'=>$metadata,'params'=>$output]);
                                    return $content;
                            endif;
                            $a = Twig::find()->where(['renderer'=>$matches[1],'theme_id'=>$theme_id])->one();
                            if($a!=null):                                
                                    $content = $twig->render($a->filename,['app'=>Yii::$app,'metadata'=>$metadata]);
                                    return $content;
                               
                            else:
                                return "";
                            endif;
                            //$content= $twig->render(Twig::find()->where(['renderer'=>$matches[1],'theme_id'=>$theme_id])->one()->filename,['app'=>Yii::$app,'metadata'=>$metadata]);
                            //return $content;
                            //return $replacer;
                    },$array); 
        
        
        $array = preg_replace_callback($pattern_map,function($matches){
                    $argos = explode(":",$matches[1]);
                    if($argos[0]=="class"):
                       $cat_name = preg_replace('/\s+/', '', $argos[1]);
                       $ad = ClassSetup::find()->where(['name'=>trim($cat_name)])->one();
                       if($ad<>null):
                        return $ad->alias;
                       else:
                        return $argos[1];
                       endif;
                       
                    endif;
                    if($argos[0]=="property"):
                       $cat_name = preg_replace('/\s+/', '', $argos[1]);
                       $ad = ClassAttributes::find()->where(['name'=>trim($argos[1])])->one();
                       if($ad<>null):
                        return $ad->alias;
                       else:
                        return $argos[1];
                       endif;                       
                    endif;
                    
        },$array);
        
        $array = preg_replace_callback($pattern_submit,function ($matches) {
                            $argos = explode("|",$matches[1]);
                            return "";
        },$array);
        
         
        $this->owner->{$field}=$array;   
        
        
        endforeach;
    }
    
}