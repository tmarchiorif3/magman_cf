<?php
class ComicUserL2Controller extends BaseController {
	protected $layout = 'layouts.master';

	public function create() {
		$series_id = Input::get('series_id');
		$number  = Input::get('number');
		$user_id = Input::get('user_id');
		$series = Series::find($series_id);
		$comics = $series->listActive()->where('number','=',$number)->get();
		if(count($comics) > 1){
			//TODO warning, no more than one number for series should be present!
		}else{
			$comic_id = 0;
			foreach($comics as $comic){
				$comic_id = $comic -> id;
			$comicUser = new ComicUser;
			$comicUser -> comic_id = $comic_id;
			$comicUser -> user_id = $user_id;
			$comicUser -> price = $comic -> price;
			$comicUser -> save();
			}
		}
		return Redirect::to('boxes/' . $user_id);
	}

	public function update() {
		$id = Input::get('id');
		$series = Series::find($id);
		$series -> name = Input::get('name');
		$series -> version = Input::get('version');
		$series -> author = Input::get('author');
		if (Input::get('type_id') != null)
			$series -> type_id = Input::get('type_id');
		if (Input::get('subtype_id') != null)
			$series -> subtype_id = Input::get('subtype_id');
		if (Input::get('active'))
			$series -> active = 1;
		else
			$series -> active = 0;
		$series -> save();
		return Redirect::to('series/' . $id);
	}

	public function delete() {
		$id = Input::get('id');
		$user_id = Input::get('user_id');
		$comics = ComicUser::whereRaw('comic_id = ' . $id . ' and user_id = ' . $user_id) -> get();
		foreach ($comics as $comic) {
			$comic -> active = '0';
			$comic -> update();
		}
		return Redirect::to('boxes/' . $user_id);
	}

	public function buy() {
		$id = Input::get('id');
		$user_id = Input::get('user_id');
		$comics = ComicUser::whereRaw('comic_id = ' . $id . ' and user_id = ' . $user_id) -> get();
		foreach ($comics as $comic) {
			$timestamp = date("Y-m-d H:i:s", time());
			$comic -> buy_time = $timestamp;
			$comic -> state_id = 3;
			$comic -> update();
		}
		return Redirect::to('boxes/' . $user_id);
	}
}
?>