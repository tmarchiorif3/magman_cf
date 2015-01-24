<?php
class UserController extends BaseController {

	/**
	 * Show the page for the addiction of a series.
	 */
	public function box() {
		$inv_state = $this->module_state('inventory');
		$series = SeriesUser::whereRaw('active = 1 and user_id =' . Auth::id())->get();
		$comics = ComicUser::whereRaw('state_id < 3 and active = 1 and user_id = ' . Auth::id())->get();
		$user = User::find(Auth::id());
		$due = $this->due($user);
		$this -> layout -> content = View::make('user/box', array('series' => $series,'user' => $user,'due' => $due,'comics' => $comics,'inv_state' => $inv_state));
	}

	/*
	 * Displays the home page for an user of the platform
	 */
	public function userHome() {
		$inv_state = $this->module_state('inventory');
		$one_week_earlier = date('Y-m-d',strtotime('-1 week'));
		$news = Comic::whereRaw("active = 1 and arrived_at > '" . $one_week_earlier . "'")->get();
		$comics = ComicUser::whereRaw('state_id < 3 and active = 1 and user_id = ' . Auth::id())->get();
		$user = User::find(Auth::id());
		$due = $this->due($user);
		$this -> layout -> content = View::make('user/homePage',array('news' => $news,'user' => $user,'due' => $due,'comics' => $comics,'inv_state' => $inv_state));
	}

	public function userProfile(){
		if(Input::get('old_pass') != null)
			echo "ciao";
		else
		$this -> layout -> content = View::make('user/profilePage',array('user' => Auth::user()));
	}

	public function due($user){
		$inv_state = $this->module_state('inventory');
		$due = 0;
		$discount = $user->discount;
		foreach ($user->listComics()->whereRaw('state_id < 3 and active = 1')->get() as $comic) {
			if($comic->comic->state == 2) {
				if (($comic->comic->available > 0 && $inv_state) || (!$inv_state && $comic->comic->state == 2))
					$due += round($comic->price, 2);
			}
		}
		return $due-($due*$discount/100);
	}

	/*
	 * Displays the series page
	 */
	public function listSeries() {
		$series = Series::where('active','=','1')->get();
		$this -> layout -> content = View::make('user/listSeries', array('series' => $series));
	}

	public function viewSeries($series_id) {
		$series = Series::find($series_id);
		$comics = Comic::whereRaw('active = 1 and series_id = ' . $series_id) -> get();
		if ($series != null && $series -> active)
			$this -> layout -> content = View::make('user/viewSeries',
			 array('series' => $series,'comics' => $comics));
		else
			return Redirect::to('series');
	}

	public function contactTheShop(){
		$this -> layout -> content = View::make('user/contactTheShop');
	}

	public function module_state($module_description){
		$modules = Modules::where('description','=',$module_description)->get();
		$state = 0;
		if(count($modules)==1) {
			foreach($modules as $module){
				$state = $module->active;
			}
		}
		return $state;
	}
}
?>