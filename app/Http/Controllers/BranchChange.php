<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use app\Models\checks;
use Illuminate\Support\Facades\DB;

class BranchChange extends Controller
{

   //storepref to store priority data in database
    public function storepref(Request $request)
    {

        $DataToStore=array();                                 //converting object data into array 
        for ($x = 0; $x <= 6; $x++) {
            if($request[$x]==NULL){
                break;
            }
            $DataToStore[$x]= $request[$x];
          }
          
          $id=$DataToStore[count($DataToStore)-1];             //extracting admission no from data
          $r1=array();
        foreach($id as $key=>$value){
            $r1[$key]=$value;
          }
          $admn=$r1["course"];
         
          //creating log in branchchangelog table for requested admission no
          DB::insert('INSERT INTO branchchangelog(id,status) VALUES (?,?)' ,[$admn,'pending review']);
          
          //inserting all priority filled by user into database
        for($x=0;$x<count($DataToStore)-1;$x++){
            $r1=array();
            foreach($DataToStore[$x] as $key=>$value){
                $r1[$key]=$value;
            }
            DB::insert('INSERT INTO prioritylist(admn_no, p_id, priority, course_type) VALUES (?,?,?,?)' ,[$admn,$r1["priority"],$r1["branch"],$r1["course"]]);
          }


        return 'data updatated';
    }


    //to check if user is eligible for branch change or not
    protected function checknew($id){
        $row = DB::table('final_semwise_marks_foil_freezed')->where('admn_no', $id)->get();      //getting all result of user 
        $row1=DB::table('reg_regular_form')->get();            //getting data of pre-registration of student
        if ($row) {
           
            $flag=0;
            
            if(count($row)==2 && count($row1)==3){
                foreach($row as $iter){
                    if($iter->core_status=="FAIL"){
                        $flag=1;
                    }
                }
                if($flag==0){
                    $branchlist=DB::table('branchlist')->get();
                    $list=array();
                    $list["type"]="eligible";
                    foreach($branchlist as $key=>$value){
                        $list[$key]=$value;
                    }
                    return $list;
                }
                $list=array();
                    $list["type"]="not eligible";
                   
                   
                    return $list;
                
            }
            
           
        } else {
            $list=array();
            $list["type"]="not eligible";
           
           
            return $list;
        }
    }

    //to check if user has already applied for branchchange or not
    protected function checkexisting($id){
        $row = DB::table('branchchangelog')->where('id', $id)->get();
        $c=count($row);
        if ($c==0) {
            return 1;
        } else {
            return 0;
        }
    }


    //main check function
    public function check(Request $request)
    {
        $item=$request->input('id');
       
        $existing=$this->checkexisting($item);
        
        if($existing){
            $verdict=$this->checknew($item);
        }
        else{
            $status= DB::table('branchchangelog')->where('id', $item)->get();
            
            $prlist=$status[0]->status=="approved"?(DB::table('branchchange_approved')->where('admn_no',$item)->get()):(DB::table('prioritylist')->where('admn_no',$item)->get());
            
            
            $list=array();
                    $list["type"]="already applied";
                    $list["status"]=$status[0]->status;
                    foreach($prlist as $key=>$value){
                        $list[$key]=$value;
                    }
                    
                    return $list;
        }

        return $verdict;
        
    }
    
}
