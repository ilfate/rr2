<?php

use Illuminate\Support\Facades\Redirect;

class HomeController extends BaseController {



	public function getIndex()
	{

	}

    /**
     * creating wizards page
     *
     * @return \Illuminate\View\View
     */
    public function getCreateWizard()
    {
        $wizardsConfig = Config::get('wizards.wizards');
        return View::make('wizard.createWizard')->with('wizardsConfig', $wizardsConfig);
    }

    /**
     * Coming from form to create Wizard
     *
     * @return mixed
     */
    public function postCreateWizard()
    {
        $wizardTypes = Config::get('wizards.types');
        $input = Input::all();
        $rules = array(
            'name' => 'required|alpha_num|between:4,18',
            'type' => 'required|in:' . implode(',', $wizardTypes)
        );

        $validator = Validator::make($input, $rules);
        if ($validator->passes())
        {

            $wizard = new Wizard();
            $data = array(
                'user_id' => Auth::user()->id,
                'name' => $input['name'],
                'class' => $input['type'],
                'data' => ''
            );
            $wizard->create($data)->with('message', 'Congratulations now you have a wizard!');

            return Redirect::to('home');
        }
        return Redirect::to('wizard/create-wizard')
            ->withInput()
            ->withErrors($validator)
            ->with('message', 'There were validation errors.');
    }

}