<?php

use Game\GameExecuter;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class LogHelper extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'command:log';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$logHelper = new \Game\LogHelper((int) $this->argument('userId'), (int) $this->argument('battleMapId'), $this->argument('params'));
        $logHelper->run();
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('userId', InputArgument::REQUIRED, 'User Id.'),
			array('battleMapId', InputArgument::REQUIRED, 'Battle map Id.'),
			array('params', InputArgument::OPTIONAL, 'Params'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}