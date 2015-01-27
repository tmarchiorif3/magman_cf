<?php
class ComicUserController extends BaseController {

    public function create()
    {
        $this -> layout = null;
        $series_id = Input::get('single_series_id');
        $comic_id = Input::get('single_number_id');
        $user_id = Input::get('user_id');
        $series = Series::find($series_id);
        if (count($series) > 0) {
            $comics = $series->listActive()->where('id', '=', $comic_id)->get();
            if (count($comics) > 1) {
				echo "qui!";
                //TODO warning, no more than one number for series should be present!
            } else {

                foreach ($comics as $comic) {
					Input::merge(array('comic_id' => $comic_id));
					Input::merge(array('price' => $comic->price));
                    $comic_id = $comic->id;
					$new = Input::all();
                    $comicUser = new ComicUser;
					if ($comicUser->validate($new)) {
						$comicUser->comic_id = $comic_id;
						$comicUser->user_id = $user_id;
						$comicUser->price = $comic->price;
						$comicUser->save();
					}else{
						echo $comicUser->errors();
						$errors = $comic->errors();
						return Redirect::to('boxes/' . $user_id)->withErrors($errors);
					}
                }
            }
        }
        return Redirect::to('boxes/' . $user_id);
    }

	public function update() {
		$cu_id = Input::get('cu_id');
		$u_id = Input::get('user_id');
		$comicUser = ComicUser::find($cu_id);
		$comicUser -> price = Input::get('price');
		if (Input::get('active'))
			$comicUser -> active = 1;
		else
			$comicUser -> active = 0;
		$comicUser -> update();
		return Redirect::to('boxes/' . $u_id);
	}

	public function delete() {
		$id = Input::get('id');
		$user_id = Input::get('user_id');
		$comics = ComicUser::whereRaw('id = ' . $id . ' and user_id = ' . $user_id) -> get();
		foreach ($comics as $comic) {
			$comic -> active = '0';
			$comic -> update();
		}
		return Redirect::to('boxes/' . $user_id);
	}

	public function buy() {
		$id = Input::get('id');
		$user_id = Input::get('user_id');
		$comics = ComicUser::whereRaw('id = ' . $id . ' and user_id = ' . $user_id) -> get();
		foreach ($comics as $comicUser) {
			$timestamp = date("Y-m-d H:i:s", time());
			$comicUser -> buy_time = $timestamp;
			$comicUser -> state_id = 3;
			$comic = $comicUser -> comic;
			$comic -> available -= 1;
			$comicUser -> update();
			$comic -> update();
		}
		 return Redirect::to('boxes/' . $user_id);
	}

}
?>