<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class NunaTest extends Nuna
{

	protected $modelname;
	protected $modelname_short;
	protected $message;
	protected $messages;
	protected $asserts;

	public function __construct($runMode = false, $profilerMode = false)
	{
		parent::__construct($runMode, $profilerMode);
		
		$this->load->library('unit_test');
		$this->load->helper('language');

		$this->modelname =$_SERVER['SCRIPT_NAME'];
		$this->modelname_short = basename($_SERVER['SCRIPT_NAME'], '.php');
		$this->messages = array();
	}

	public function index()
	{
		$this->_show_all();
	}

	protected function show_results()
	{
		$this->_run_all();
		$data['modelname'] = $this->modelname;
		$data['results'] = $this->unit->result();
		$data['messages'] = $this->messages;
		$this->output
			->append_output($this->view_results($data));
	}

	protected function _show_all()
	{
		$this->_run_all();
		$data['modelname'] = $this->modelname;
		$data['results'] = $this->unit->result();
		$data['messages'] = $this->messages;

		$this->output
			->append_output($this->view_head())
			->append_output($this->view_results($data))
			->append_output($this->view_footer());
	}

	protected function _show($method)
	{
		$this->_run($method);
		$data['modelname'] = $this->modelname;
		$data['results'] = $this->unit->result();
		$data['messages'] = $this->messages;

		$this->output
			->append_output($this->view_head())
			->append_output($this->view_results($data))
			->append_output($this->view_footer());
	}
	
	protected function view_head()
	{
		$output = array();
		$joiner = '';
		
		if(is_cli()) {
			$joiner = "\n";
			$output[] = 'Nuna Unit Tests:';
			$output[] = '================';
			$output[] = '';
		} else {
			$output[] = '<html>';
			$output[] = '<head>';
			$output[] = '<title>Unit test results</title>';
			$output[] = '<style type="text/css">';
			$output[] = '* { font-family: Arial, sans-serif; font-size: 9pt }';
			$output[] = '#results { width: 100% }';
			$output[] = '.err, .pas { color: white; font-weight: bold; margin: 2px 0; padding: 5px; vertical-align: top; }';
			$output[] = '.err { background-color: red }';
			$output[] = '.pas { background-color: green }';
			$output[] = '.detail { padding: 8px 0 8px 20px }';
			$output[] = 'h1 { font-size: 12pt }';
			$output[] = 'a:link, a:visited { text-decoration: none; color: white }';
			$output[] = 'a:active, a:hover { text-decoration: none; color: black; background-color: yellow }';
			$output[] = '</style>';
			$output[] = '</head>';
			$output[] = '<body>';
			$output[] = '<h1>Nuna Unit Tests:</h1>';
			$output[] = '<ol>';
		}
		
		return implode($joiner, $output);
	}
	
	protected function view_results($data)
	{
		$results = $data['results'];
		$messages = $data['messages'];
		
		$i = 0;
		$output = array();

		foreach ($results as $result) {
			if(is_cli()) {
				if ($result[lang('ut_result')] == lang('ut_passed')) {
					$output[] = sprintf('%d. [%s] %s', $i + 1, strtoupper(lang('ut_passed')), strip_tags($result[lang('ut_test_name')]));
				} else {
					$output[] = sprintf('%d. [%s] %s', $i + 1, strtoupper(lang('ut_failed')),strip_tags($result[lang('ut_test_name')]));
				}
				
				if(!empty($messages[$i])) {
					$output[] = '<!- message ->';
					$output[] = $messages[$i];
					$output[] = '<!----------->';
				}
			} else {
				$output[] = '<li>';
				if ($result[lang('ut_result')] == lang('ut_passed')) {
					$output[] = '<div class="pas">';
					$output[] = sprintf('[%s] %s', strtoupper(lang('ut_passed')), $result[lang('ut_test_name')]);
				} else {
					$output[] = '<div class="err">';
					$output[] = sprintf('[%s] %s', strtoupper(lang('ut_failed')), $result[lang('ut_test_name')]);
				}
	
				if(!empty($messages[$i])) {
					$output[] = '<div class="detail">' . $messages[$i] . '&nbsp;</div>';
				}
				
				$output[] = '</div>';
				$output[] = '</li>';
			}
			
			$i++;
		}
		
		$output[] = '';
		
		return implode(is_cli() ? "\n" : '', $output);
	}
	
	protected function view_footer()
	{
		$output = array();
		
		if(!is_cli()) {
			$output[] = '</ol>';
			$output[] = '<br />';
			$output[] = sprintf('<strong>All tests completed in %s seconds</strong>', $this->benchmark->elapsed_time('total_execution_time_start', 'total_execution_time_end'));
			$output[] = '<br />';
			$output[] = '<br />';
			$output[] = '<br />';
			$output[] = '<br />';
			$output[] = '</body>';
			$output[] = '</html> ';
		}
		
		return implode('', $output);
	}

	protected function _run_all()
	{
		foreach ($this->_get_test_methods() as $method)
		{
			$this->_run($method);
		}
	}

	protected function _run($method)
	{
		// Reset message from test
		$this->message = '';

		// Reset asserts
		$this->asserts = TRUE;

		// Run cleanup method _pre
		$this->_pre();

		// Run test case (result will be in $this->asserts)
		$this->$method();

		// Run cleanup method _post
		$this->_post();

		// Set test description to "model name -> method name" with links
		$test_class_segments = __FILE__ . strtolower($this->modelname_short);
		$test_method_segments = strncmp($method, 'test_', 5) == 0 ? substr($method, 5) : substr($method, 4);
		$desc = '<a href="' . $this->modelname . '">' . $this->modelname_short . '</a>' . ' -> ' .  '<a href="' . $this->modelname . '/' . $test_method_segments . '">' . $test_method_segments . '</a>'; // anchor($test_method_segments, substr($method, 5));

		$this->messages[] = trim($this->message);

		// Pass the test case to CodeIgniter
		$this->unit->run($this->asserts, TRUE, $desc);
	}

	protected function _get_test_methods()
	{
		$methods = get_class_methods($this);
		$testMethods = array();
		foreach ($methods as $method) {
			if (substr(strtolower($method), 0, 4) == 'test') {
				$testMethods[] = $method;
			}
		}
		return $testMethods;
	}

	/**
	 * Remap function (CI magic function)
	 * 
	 * Reroutes any request that matches a test function in the subclass
	 * to the _show() function.
	 * 
	 * This makes it possible to request /my_test_class/my_test_function
	 * to test just that single function, and /my_test_class to test all the
	 * functions in the class.
	 * 
	 */
	protected function _remap($method)
	{
		if (method_exists($this, 'test_' . $method)) {
			$this->_show('test_' . $method);
		} elseif(method_exists($this, 'test' . $method)) {
			$this->_show('test' . $method);
		} elseif(method_exists($this, $method)) {
			$this->$method();
		} else {
			show_404();
		}
	}


	/**
	 * Cleanup function that is run before each test case
	 * Override this method in test classes!
	 */
	protected function _pre() { }

	/**
	 * Cleanup function that is run after each test case
	 * Override this method in test classes!
	 */
	protected function _post() { }


	protected function _fail($message = null) {
		$this->asserts = FALSE;
		if ($message != null) {
			$this->message = $message;
		}
		return FALSE;
	}
	
	protected function fail($message = null) {
		return $this->_fail($message);
	}
	
	protected function _assert_true($assertion) {
		if($assertion) {
			return TRUE;
		} else {
			$this->asserts = FALSE;
			return FALSE;
		}
	}
	
	protected function assertTrue($assertion) {
		return $this->_assert_true($assertion);
	}
	
	protected function _assert_false($assertion) {
		if($assertion) {
			$this->asserts = FALSE;
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	protected function assertFalse($assertion) {
		return $this->_assert_false($assertion);
	}
	
	protected function _assert_true_strict($assertion) {
		if($assertion === TRUE) {
			return TRUE;
		} else {
			$this->asserts = FALSE;
			return FALSE;
		}
	}
	
	protected function assertTrueStrict($assertion) {
		return $this->_assert_true_strict($assertion);
	}
	
	protected function _assert_false_strict($assertion) {
		if($assertion === FALSE) {
			return TRUE;
		} else {
			$this->asserts = FALSE;
			return FALSE;
		}
	}
	
	protected function assertFalseStrict($assertion) {
		return $this->_assert_false_strict($assertion);
	}
	
	protected function _assert_equals($base, $check) {
		if($base == $check) {
			return TRUE;
		} else {
			$this->asserts = FALSE;
			return FALSE;
		}
	}
	
	protected function assertEquals($base, $check) {
		return $this->_assert_equals($base, $check);
	}
	
	protected function _assert_not_equals($base, $check) {
		if($base != $check) {
			return TRUE;
		} else {
			$this->asserts = FALSE;
			return FALSE;
		}
	}

	protected function assertNotEquals($base, $check) {
		return $this->_assert_not_equals($base, $check);
	}
	
	protected function _assert_equals_strict($base, $check) {
		if($base === $check) {
			return TRUE;
		} else {
			$this->asserts = FALSE;
			return FALSE;
		}
	}

	protected function assertEqualsStrict($base, $check) {
		return $this->_assert_equals_strict($base, $check);
	}
	
	protected function _assert_not_equals_strict($base, $check) {
		if($base !== $check) {
			return TRUE;
		} else {
			$this->asserts = FALSE;
			return FALSE;
		}
	}

	protected function assertNotEqualsStrict($base, $check) {
		return $this->_assert_not_equals_strict($base, $check);
	}
	
	protected function _assert_empty($assertion) {
		if(empty($assertion)) {
			return TRUE;
		} else {
			$this->asserts = FALSE;
			return FALSE;
		}
	}

	protected function assertEmpty($assertion) {
		return $this->_assert_empty($assertion);
	}
	
	protected function _assert_not_empty($assertion) {
		if(!empty($assertion)) {
			return TRUE;
		} else {
			$this->asserts = FALSE;
			return FALSE;
		}
	}
	
	protected function assertNotEmpty($assertion) {
		return $this->_assert_not_empty($assertion);
	}
	
	protected function debug($data) {
		$data = $data === false ? '(bool false)' : $data;
		$data = $data === null ? '(php null)' : $data;
		$data = $data === true ? '(bool true)' : $data;
		
		$this->message .= (print_r($data, true) . PHP_EOL);
	}

}