<?php
class HomePageController extends BaseController {

	/**
	 * Show the profile for the given user.
	 */
	protected $layout = 'layouts.master';
	public function index() {
		//$comic = Comic::where('name', 'LIKE', '%città%')
		// ->get();
		$this -> layout -> content = View::make('homePage');
	}

	/*
	 * Displays the home page for an administrator of the platform
	 */
	public function adminHome() {
		/*
		 * Insolvent and defaulting users:
		 * - insolvent: due > 5
		 * - defaulting: not buying for at least 3 months
		 */
		$boxes = User::where('active','=','1')->get();
		$insolvency_threshold = ShopConf::find(1);
		$defaulting_threshold = ShopConf::find(2);
//		$toOrder = DB::select('select s.id as sid,c.id as cid,s.name,s.version,c.number, c.available, count(*) as to_order, (count(*)-c.available) as need FROM (comic_user as cu LEFT JOIN comics as c ON cu.comic_id = c.id) LEFT JOIN series as s ON  s.id = series_id WHERE cu.state_id = 1 and cu.active = 1  GROUP BY cu.comic_id');
		$toOrder = DB::select('select s.id as sid,c.id as cid,s.name, s.version,c.number, c.available, cu.to_order, (cu.to_order-c.available) as need FROM ((SELECT count(*) as to_order, comic_id FROM comic_user WHERE state_id = 1 and active = 1 GROUP BY comic_id) as cu LEFT JOIN comics as c ON cu.comic_id = c.id) LEFT JOIN series as s ON  s.id = series_id WHERE  (cu.to_order-c.available) > 0');
		$insolvents = $this -> buildInsolventArray($boxes,$insolvency_threshold);
		$defaultings = $this -> buildDefaultingArray($boxes,$defaulting_threshold);
		$this -> layout -> content = View::make('admin/homePage',array('insolvents' => $insolvents,'defaultings' => $defaultings,'to_order' => $toOrder));
	}

	/*
	 * Displays the home page for an user of the platform
	 */
	public function userHome() {
		$this -> layout -> content = View::make('user/userHomePage');
	}
	
	public function buildInsolventArray($boxes,$insolvency_treshold){
		$insolvent = array();
		foreach ($boxes as $box) {
			// check available comics and due
			$comics = $box -> listComics() -> whereRaw('state_id < 3 and active = 1') -> get();
			$due_counter = 0;
			foreach ($comics as $comic) {
				if ($comic -> comic -> available > 1) {
					$due_counter += round($comic -> price, 2);
				}
			}
			$due_counter = $due_counter - ($due_counter * $box -> discount / 100);
			if($due_counter > $insolvency_treshold -> value){
				$insolvent = array_add($insolvent, $box -> name . " " . $box -> surname, 'Insolvente (' . $due_counter . '€)');
				// echo $box->name . " insolvent: " . $due_counter . "<br>";
			}
		}
		return $insolvent;
	}
	
	public function buildDefaultingArray($boxes,$defaulting_threshold){
		$defaulting = array();
		foreach ($boxes as $box){
			$last_buy = $box->lastBuy->max('buy_time');
			if(strtotime("-" . $defaulting_threshold -> value . " days") > strtotime($last_buy) && $last_buy != null){
				// echo $box->name . " defaulting: " . $box->lastBuy->max('buy_time') . "<br>";
				$defaulting = array_add($defaulting, $box -> name . " " .
				 $box -> surname, 'Disperso (' . date('d/m/Y',strtotime($last_buy)) . ')');
			}
		}
		return $defaulting;
	}
}
?>