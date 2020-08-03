<?php

namespace App\Http\Controllers\Site;

use App\club_builder;
use App\club_temporary_builder;
use App\Clubs;
use App\Http\Controllers\Controller;
use App\line_up_types;
use App\Models\Translates;
use App\pes_players;
use App\player_positions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BuilderController extends Controller
{
    public function get_builder(){

        $translates=Translates::where('page','builder')->get();
        $notification="";

        $max_point_builder=DB::table('eml_dynamic_variables')->
        where('name','max_point_builder')->first();

        $club=Clubs::where('user_id',Auth::user()->id)->first();
        $club_id=$club->id;

        $club_submited=Clubs::where('id',$club_id)->first()->confirmed;

        if($club_submited==1){

            $club_builder=club_temporary_builder::where('club_id',$club_id)->delete();
            $club_builder_submited=club_builder::where('club_id',$club_id)->where('is_submited',1)->get();

            foreach ($club_builder_submited as $items) {
                club_temporary_builder::create([
                    'club_id' => $club_id,
                    'line_up' => $items->line_up,
                    'builder_player' => $items->builder_player,
                    'player_point' => pes_players::find($items->builder_player)->ovr,
                    'club' => pes_players::find($items->builder_player)->club,
                    'builder_pos' => $items->builder_pos,
                    'parent_pos' => $items->parent_pos,
                ]);
            }
            $notification="This builder is submitted by admin";

        }

        $club_builder=club_temporary_builder::where('club_id',$club_id)->get();
        $line_up_types=line_up_types::all();

        $sum=club_temporary_builder::where('club_id',$club_id)->sum('player_point');

        $point=$max_point_builder->value-$sum;


        $pos_player_pb_gk=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_gk')->first();
        $pos_player_pb_lb=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_lb')->first();
        $pos_player_pb_cb1=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_cb1')->first();
        $pos_player_pb_cb2=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_cb2')->first();
        $pos_player_pb_rb=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_rb')->first();
        $pos_player_pb_cm1=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_cm1')->first();
        $pos_player_pb_cm2=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_cm2')->first();
        $pos_player_pb_lm=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_lm')->first();
        $pos_player_pb_am=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_am')->first();
        $pos_player_pb_rm=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_rm')->first();
        $pos_player_pb_cf=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_cf')->first();

        $pos_player_pb_subs1=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_subs1')->first();
        $pos_player_pb_subs2=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_subs2')->first();
        $pos_player_pb_subs3=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_subs3')->first();
        $pos_player_pb_subs4=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_subs4')->first();
        $pos_player_pb_subs5=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_subs5')->first();
        $pos_player_pb_subs6=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_subs6')->first();
        $pos_player_pb_subs7=club_temporary_builder::where('club_id',$club_id)->where('parent_pos','pb_subs7')->first();


        $pes_players=pes_players::orderBy('ovr','desc')->limit(200)->get();

        $positions=player_positions::get();

        $nationalities=pes_players::distinct('nation')->select('nation')->orderBy('nation')->get();

        $clubs=pes_players::distinct('club')->select('club')->orderBy('club')->get();



        return view('site.builder',
            compact('player','pes_players','positions','nationalities','clubs','max_point_builder',
                'club_builder','line_up_types',
                'notification',
                'club',
                'pos_player_pb_subs1',
                'pos_player_pb_subs2',
                'pos_player_pb_subs3',
                'pos_player_pb_subs4',
                'pos_player_pb_subs5',
                'pos_player_pb_subs6',
                'pos_player_pb_subs7',
                'pos_player_pb_gk',
                'pos_player_pb_lb',
                'pos_player_pb_cb1',
                'pos_player_pb_cb2',
                'pos_player_pb_rb',
                'pos_player_pb_cm1',
                'pos_player_pb_cm2',
                'pos_player_pb_lm',
                'pos_player_pb_am',
                'pos_player_pb_rm',
                'pos_player_pb_cf',
                'point',
                'translates'));


    }

    public function submitbuilder(Request $request){

        $club=Clubs::where('user_id',Auth::user()->id)->first();
        $club_id=$club->id;
        $max_point_builder=DB::table('eml_dynamic_variables')->
        where('name','max_point_builder')->first();

        $club_builder_sending=club_temporary_builder::where('club_id',$club_id)->get();

        $sum=club_temporary_builder::where('club_id',$club_id)->sum('player_point');

        if($sum<=$max_point_builder->value) {

            $delete_old_forsubmiting = club_builder::where('club_id', $club_id)->where('is_submited', 0)->delete();

            foreach ($club_builder_sending as $items) {

                $club = pes_players::find($items->builder_player)->club;
                $count = count(club_temporary_builder::where('club_id', $club_id)->where('club', $club)->get());
                if ($count <= 2 || $club = "") {

                    club_builder::create([
                        'club_id' => $club_id,
                        'line_up' => $items->line_up,
                        'builder_player' => $items->builder_player,
                        'player_point' => pes_players::find($items->builder_player)->ovr,
                        'club' => pes_players::find($items->builder_player)->club,
                        'builder_pos' => $items->builder_pos,
                        'parent_pos' => $items->parent_pos,
                    ]);
                } else return redirect('/player/builder')->with('notification', 'Your team has more than 2 footballers belonging to the same club  ');
            }
            $is_club_submited=Clubs::where('id',$club_id)->update(['builder_submit'=>1,'confirmed'=>0]);


            return redirect('/player/builder')->with('notification', 'Your team is sent to admin for confirmation');
        }

        return redirect('/player/builder')->with('notification', 'Your Point is Greater than Max Point' );

    }

    public function change_line_up(Request $request){


        $club=Clubs::where('user_id',Auth::user()->id)->first();
        $club_id=$club->id;

        $builder_player=$request->builder_player;
        $builder_pos=$request->builder_pos;
        $parent_pos=$request->parent_pos;
        $success=true;
        $error="";

        $club_builder_all=club_temporary_builder::where('club_id',$club_id)->get();
        if(count($club_builder_all)>0) $delete_all=club_temporary_builder::where('club_id',$club_id)->delete();

        if($builder_player)
            foreach ($builder_player as $key=>$item) {
                if ($item) {
                    $insert_all = club_temporary_builder::create([
                        'club_id' => $club_id,
                        'line_up' => $request->get('lineup_select'),
                        'builder_player' => $item,
                        'player_point' => pes_players::find($item)->ovr,
                        'club' => pes_players::find($item)->club,
                        'builder_pos' => $builder_pos[$key],
                        'parent_pos' => $parent_pos[$key]

                    ]);
                    if($insert_all) {$success=true;}
                    else { $success=false; $error="Problem adding $builder_pos[$key] player";break;}
                }
            }


        $result['success']=$success;
        $result['error']=$error;


        echo json_encode($result);
        exit;

    }

    public function post_builder_search(Request $request){

        $club=Clubs::where('user_id',Auth::user()->id)->first();
        $club_id=$club->id;
        $max_point_builder=DB::table('eml_dynamic_variables')->
        where('name','max_point_builder')->first();

        /* $club_builder_all=club_temporary_builder::where('club_id',$club_id)->get();
         if(count($club_builder_all)>0) $delete_all=club_temporary_builder::where('club_id',$club_id)->delete();

         $builder_player=$request->builder_player;
         $builder_pos=$request->builder_pos;
         $parent_pos=$request->parent_pos;
         if($builder_player)
             foreach ($builder_player as $key=>$item){
                 if($item) {
                     $club = pes_players::find($item)->club;
                     $count = count(club_temporary_builder::where('club_id', $club_id)->where('club', $club)->get());
                     if ($count <= 2 || $club = "") {
                         $insert_all = club_temporary_builder::create([
                             'club_id' => $club_id,
                             'line_up' => $request->get('lineup_select'),
                             'builder_player' => $item,
                             'player_point' => pes_players::find($item)->ovr,
                             'club' => pes_players::find($item)->club,
                             'builder_pos' => $builder_pos[$key],
                             'parent_pos' => $parent_pos[$key]
                         ]);
                     }
                 }
             }*/

        $name=$request->name;
        $position=$request->positions;
        $nationality=$request->nationalities;
        $club=$request->clubs;
        $ovr=$request->ovr;

        $ovr_array=explode(';',$ovr);

        $search='';

        $data=pes_players::where('ovr','>=',$ovr_array[0])->where('ovr','<=',$ovr_array[1]);
        if($name)  $data=$data->where('name','like', '%'.$name.'%');
        if($position) $data=$data->where('pos',$position);
        if($nationality) $data=$data->where('nation',$nationality);
        if($club) $data=$data->where('club',$club);

        $builder_player=$request->builder_player;


        foreach ($builder_player as $item){
            if($item)  $data=$data->where('id','<>',$item);
        }


        $data=$data->orderBy('ovr','desc')->limit(200)->get();


        echo json_encode($data);
        exit;


    }

    public function post_builder(Request $request){

        $name=$request->name;
        $position=$request->positions;
        $nationality=$request->nationalities;
        $club=$request->clubs;
        $ovr=$request->ovr;

        $ovr_array=explode(';',$ovr);

        $data=pes_players::where('ovr','>=',$ovr_array[0])->where('ovr','<=',$ovr_array[1]);
        if($name)  $data=$data->where('name',$name);
        if($position) $data=$data->where('pos',$position);
        if($nationality) $data=$data->where('nation',$nationality);
        if($club) $data=$data->where('club',$club);

        $data=$data->orderBy('ovr','desc')->limit(200)->get();

        return $data;


    }
    public function post_builder_select($pos,$id,Request $request){

        if($pos!=1) $data=pes_players::where('pos',$pos); else $data=pes_players::where('pos','<>','');
        if($id)  $data=$data->where('id','<>',$id);
        $builder_player=$request->builder_player;
        foreach ($builder_player as $item){
            if($item)  $data=$data->where('id','<>',$item);
        }
        $data=$data->orderBy('ovr','desc')->limit(200)->get();

        echo json_encode($data);
        exit;

    }

    public function club_temporary_builder($id,Request $request){

        $club=Clubs::where('user_id',Auth::user()->id)->first();
        $club_id=$club->id;
        $max_point_builder=DB::table('eml_dynamic_variables')->
        where('name','max_point_builder')->first();

        $data=club_temporary_builder::where('club_id',$club_id)->
        where(function ($q) use ($id,$request){

            $q-> where('builder_player',$id)->
            orWhere('parent_pos',$request->get('changed_parent_pos'));
        })
            ->get();
        if(count($data)>0)  { $success=false; $error="This player or this position has been selected ";}
        else{

            $sum=club_temporary_builder::where('club_id',$club_id)->sum('player_point');
            $selected_player=pes_players::find($id);
            if(($sum+$selected_player->ovr)<=$max_point_builder->value){

                $club = pes_players::find($id)->club;
                $count = count(club_temporary_builder::where('club_id', $club_id)->where('club', $club)->get());
                if ($count <= 1 || $club = "") {

                    $club_builder_all=club_temporary_builder::where('club_id',$club_id)->get();
                    if(count($club_builder_all)>0) $delete_all=club_temporary_builder::where('club_id',$club_id)->delete();

                    $insert = club_temporary_builder::create([
                        'club_id' => $club_id,
                        'line_up' => $request->get('lineup_select'),
                        'builder_player' => $selected_player->id,
                        'player_point' => $selected_player->ovr,
                        'club' => $selected_player->club,
                        'builder_pos' => $request->get('attacked_builder_pos'),
                        'parent_pos' => $request->get('changed_parent_pos')
                    ]);
                    if ($insert) {
                        $success = true;
                        $error = "";
                    } else {
                        $success = false;
                        $error = "A problem in adding player";
                    }

                    $builder_player = $request->builder_player;
                    $builder_pos = $request->builder_pos;
                    $parent_pos = $request->parent_pos;
                    if ($builder_player)
                        foreach ($builder_player as $key => $item) {
                            if ($item) {
                                $insert_all = club_temporary_builder::create([
                                    'club_id' => $club_id,
                                    'line_up' => $request->get('lineup_select'),
                                    'builder_player' => $item,
                                    'player_point' => pes_players::find($item)->ovr,
                                    'club' => pes_players::find($item)->club,
                                    'builder_pos' => $builder_pos[$key],
                                    'parent_pos' => $parent_pos[$key]
                                ]);
                            }
                        }


                }    else {
                    $success=false;
                    $error="Selected player's Club is repeated more than 2";
                }
            }
            else {
                $success=false;
                $error="You have exceeded the number of points";
            }


        }
        $sum=club_temporary_builder::where('club_id',$club_id)->sum('player_point');
        $point=$max_point_builder->value-$sum;

        $result['success']=$success;
        $result['error']=$error;
        $result['point']=$point;

        echo json_encode($result);
        exit;

    }

    public function club_temporary_builder_delete($pos,Request $request){

        $club=Clubs::where('user_id',Auth::user()->id)->first();
        $club_id=$club->id;
        $max_point_builder=DB::table('eml_dynamic_variables')->
        where('name','max_point_builder')->first();

        $club_builder_all=club_temporary_builder::where('club_id',$club_id)->get();
        if(count($club_builder_all)>0) $delete_all=club_temporary_builder::where('club_id',$club_id)->delete();

        $builder_player=$request->builder_player;
        $builder_pos=$request->builder_pos;
        $parent_pos=$request->parent_pos;
        if($builder_player)
            foreach ($builder_player as $key=>$item){
                if($item) {
                    $club = pes_players::find($item)->club;
                    $count = count(club_temporary_builder::where('club_id', $club_id)->where('club', $club)->get());
                    if ($count <= 2 || $club = "") {
                        $insert_all = club_temporary_builder::create([
                            'club_id' => $club_id,
                            'line_up' => $request->get('lineup_select'),
                            'builder_player' => $item,
                            'player_point' => pes_players::find($item)->ovr,
                            'club' => pes_players::find($item)->club,
                            'builder_pos' => $builder_pos[$key],
                            'parent_pos' => $parent_pos[$key]
                        ]);
                    }
                }
            }

        $data=club_temporary_builder::where('club_id',$club_id)->where('parent_pos',$pos)->first();
        if($data) {
            $selected_player_ovr = $data->player_point;
            $delete = $data->delete();


            if ($delete) {
                $success = true;
                $error = "";
            } else {
                $success = false;
                $error = "1.A problem in deleting player";
            }
        }
        else {
            $success = false;
            $error = "2.A problem in deleting player";
        }
        $sum=club_temporary_builder::where('club_id',$club_id)->sum('player_point');
        $point=$max_point_builder->value-$sum;
        $result['success']=$success;
        $result['error']=$error;
        $result['point']=$point;

        echo json_encode($result);
        exit;

    }
}   
