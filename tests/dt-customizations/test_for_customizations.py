from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.common.keys import Keys
from sty import fg, bg, ef, rs
import os
import random
import string
import time
import re
import configparser
import mysql.connector

hostname = 'http://localhost:10089'

def get_my_db(config_database):
	my_db = mysql.connector.connect(
	  host = config_database['host'],
	  user = config_database['user'],
	  password = config_database['password'],
	  unix_socket = config_database['unix_socket'],
	  database = config_database['database_name'],
	)
	return my_db

def get_db_prefix():
	db_prefix = 'wp_'
	return db_prefix

def calculate_longest_output(message):
	global longest_output
	if len(message) > longest_output:
		longest_output = len(message)
		config = configparser.ConfigParser()
		config.read('config.ini')
		config['DEFAULT']['longest_output'] = str(longest_output)
		with open('config.ini', 'w') as config_file:
			config.write(config_file)

def get_space_chars(message):
	global longest_output
	space_chars = 0
	if len(message) < longest_output:
		space_chars = longest_output - len(message)
	return space_chars

def bolded(message):
	return '\033[1m%s\033[0m' % message

def wait_until_load():
	global driver
	WebDriverWait(driver, 10).until(lambda driver: driver.execute_script('return document.readyState') == 'complete')

def scroll_to_top():
	driver.find_element(By.XPATH, '//body').send_keys(Keys.CONTROL + Keys.HOME)


def test_passed():
	ok_text = '[%sOK%s]' % ( fg.green, fg.rs)
	print(f"{ok_text : >20}")


def test_not_passed(message=''):
	if message != '':
		message = ' - (%s)' % message
	error_text = '[%serror%s] %s' % (fg.red, fg.rs, message)
	print(f"{error_text : >20}")


def random_string(chars=5):
	return str(''.join(random.choices(string.ascii_uppercase + string.digits, k=chars)))

def login(username, password):
	global driver
	wait_until_load()
	send_message('Log in')
	try:
		driver.find_element('id', 'user_login').send_keys(username)
		driver.find_element('id', 'user_pass').send_keys(password)
		driver.find_element('id', 'wp-submit').click()
		test_passed()
	except:
		test_not_passed('Login failed; shutting down.')
		exit()

def send_message(message, indent=False):
	global longest_output
	indentation = ' - '
	if indent == True:
		indentation = '   └ '
	message = indentation + message
	space_chars = get_space_chars(message)
	output = message + ' ' * space_chars
	print(output, end='')
	calculate_longest_output(output)

def refresh_page():
	driver.refresh()
	print('*** Page refreshed ***')


### DATABASE FUNCTIONS - START ###	
def delete_dt_field_customizations():
	global config
	my_db = get_my_db(config['DATABASE'])
	my_cursor = my_db.cursor()
	db_prefix = get_db_prefix()
	my_cursor.execute("DELETE FROM `%soptions` WHERE option_name = 'dt_field_customizations';" % db_prefix)
	my_db.commit()
	print('*** DT Field Customizations deleted successfully ***')

### DATABASE FUNCTIONS - END ###	


def test_click(message, xpath, indent=False):
	send_message(message, indent)
	try:
		WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.XPATH, xpath)))
		driver.find_element(By.XPATH, xpath).click()
		test_passed()
	except:
		test_not_passed()

def test_click_random_from(message, xpath, indent=False):
	send_message(message, indent)
	try:
		WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.XPATH, xpath)))
		elements = driver.find_elements(By.XPATH, xpath)
		random_element = random.choice(elements)
		random_element.click()
		test_passed()
		return random_element
	except:
		test_not_passed()

def test_send_keys(message, xpath, keys, indent=False):
	wait_until_load()
	send_message(message, indent)
	try:
		driver.find_element(By.XPATH, xpath).send_keys(keys)
		test_passed()
	except:
		print(xpath)
		test_not_passed()

def test_element_present(message, xpath, indent=False):
	send_message(message, indent)
	try:
		time.sleep(1)
		driver.find_element(By.XPATH, xpath)
		test_passed()
	except:
		test_not_passed()

def test_element_not_present(message, xpath, indent=False):
	send_message(message, indent)
	try:
		time.sleep(1)
		driver.find_element(By.XPATH, xpath)
		test_not_passed()
	except:
		test_passed()

def test_checkbox_checked(message, xpath, indent=False):
	send_message(message, indent)
	if driver.find_element(By.XPATH, xpath).is_selected():
		test_passed()
	else:
		test_not_passed()

def test_element_attribute_matches(message, xpath, attribute, needle, indent=False):
	send_message(message, indent)
	try:
		element = driver.find_element(By.XPATH, xpath)
		if element.get_attribute(attribute) == needle:
			return True
	except:
		return False

def test_add_tile():
	print(bolded('\n - Add tile to post type'))
	test_click('Click "add new tile" link', "//a[@id='add-new-tile-link']" , True)
	random_tile_name = random_string(10) + ' Tile'
	random_tile_name_key = random_tile_name.lower().replace(' ', '_')
	test_send_keys('Add random New Tile Name', "//input[@id='new_tile_name']", random_tile_name, True)
	test_click('Click "Create Tile" button', "//button[@id='js-add-tile']", True)
	test_element_present("Check New Tile '%s' was added to menu" % random_tile_name, "//div[@class='field-settings-table-tile-name expandable' and @data-key='%s']" % random_tile_name_key, True)
	go_to_contacts_page()
	select_random_contact_from_contacts_page()
	test_element_present("Check '%s' tile is present on post type page" % random_tile_name, "//div[@id='%s-tile']" % random_tile_name_key, True)

def go_to_dt_customizations_page(indent=False):
	send_message('Go to DT Customizations page', indent)
	try:
		global hostname
		driver.get( hostname + '/wp-admin/admin.php?page=dt_customizations')
		wait_until_load()
		test_passed()
	except:
		test_not_passed()

def go_to_contacts_page(indent=False):
	send_message('Go to contacts page', True)
	try:
		global hostname
		driver.get( hostname + '/contacts')
		wait_until_load()
		test_passed()
	except:
		test_not_passed()

def select_random_contact_from_contacts_page(indent=False):
	send_message('Select random contact from contacts page', True)
	try:	
		global driver
		contact_links_object = driver.find_elements(By.XPATH, "//tr[@class='dnd-moved']")
		contact_links = []
		for clo in contact_links_object:
			contact_links.append(clo.get_attribute('data-link'))
		random_contact_url = random.choice(contact_links)
		driver.get(random_contact_url)
		wait_until_load()
		test_passed()
	except:
		test_not_passed()

def test_tile_presence(tile_name):
	try:
		test_element_present('Test tile presence in page', 'xpath for tile here', True)
		test_passed()
	except:
		test_not_passed()

def test_add_field_to_tile(field_type=''):
	print(bolded('\nAdd new field to tile'))
	#select_random_from('- Select', "//div[@class='field-settings-table-tile-name expandable']", True)

def get_all_tile_keys():
	tile_keys = []
	all_tile_buttons = driver.find_elements(By.XPATH, "//div[@data-modal='edit-tile']")
	for atb in all_tile_buttons:
		tile_keys.append(atb.get_attribute('data-key'))
	return tile_keys

def get_post_type():
	return driver.execute_script('return window.wpApiShare.post_type;')

def get_post_type_from_wp_admin():
	return driver.execute_script('return window.field_settings.post_type;')

def add_connection_fields(tile_key):
	test_click('Click "add new field" in "%s" tile' % tile_key, "//span[@data-parent-tile-key='%s']" % tile_key, True)
	time.sleep(1)
	Select(driver.find_element(By.XPATH, "//select[@name='new-field-type']")).select_by_value('connection')
	connection_target_dropdown = Select(driver.find_element(By.XPATH, "//select[@name='connection-target']"))
	post_types = get_all_post_types_from_connection_target_dropdown()
	current_post_type = get_post_type_from_wp_admin()
	
	for pt in post_types:
		if current_post_type == pt:
			add_connection_field_same_post_type_bidirectional(tile_key)
			add_connection_field_same_post_type_non_bidirectional(tile_key)
		if current_post_type != pt:
			add_connection_field_different_post_type(tile_key, pt)
			

	#Create connection field for post types diferent than the selected one
	random_field_name = random_string(10) + ' Field'
	random_field_name_key = random_field_name.lower().replace(' ', '_')
	test_send_keys('Add random new field name', "//input[@name='edit-tile-label']", random_field_name, True)
	
	for pt in post_types:
		print('\nAdding "%s -> %s" connection' % (tile_key, pt))
		random_field_name = random_string(10) + ' Field'
		random_field_name_key = random_field_name.lower().replace(' ', '_')
		test_click('Click "add new field" in "%s" tile' % tile_key, "//span[@data-parent-tile-key='%s']" % tile_key, True)
		test_send_keys('Add random new field name', "//input[@name='edit-tile-label']", random_field_name, True)
		#test private field
		connection_target_dropdown.select_by_value(pt)
		test_click('Adding "%s" connection type field to "%s" target post type' % (random_field_name, pt), "//button[@id='js-add-field' and @data-tile-key='%s]" % tile_key, True)
		#test if bi-directional


def get_all_post_types_from_connection_target_dropdown():
	connection_target_dropdown_values = driver.find_elements(By.XPATH, "//select[@name='connection-target']/option")
	all_post_types = []
	for ctdv in connection_target_dropdown_values:
		if ctdv.get_attribute('value').strip():
			all_post_types.append(ctdv.get_attribute('value').strip())
	return all_post_types



def add_connection_field_same_post_type_bidirectional(tile_key):
	post_type = get_post_type_from_wp_admin()
	Select(driver.find_element(By.XPATH, "//select[@name='connection-target']")).select_by_value(post_type)
	current_dt_customization_url = driver.execute_script('return window.location.href;')
	print(bolded('   └ \nAdding "%s -> %s" connection (bi-directinal)' % (post_type, post_type)))
	random_field_name = random_string(10) + ' Field'
	random_field_name_key = random_field_name.lower().replace(' ', '_')
	test_send_keys('Add random new field name', "//input[@name='edit-tile-label']", random_field_name, True)
	test_checkbox_checked('Check that "bidirectional" checkbox is checked', "//input[@id='multidirectional_checkbox']", True)
	test_click('Adding "%s" bidirectional connection type field' % random_field_name, "//button[@id='js-add-field' and @data-tile-key='%s']" % tile_key, True)
	test_element_present('Check New "bidirectional connection" type field "%s" was added to "%s" tile' % (random_field_name, tile_key), "//div[@class='field-settings-table-field-name' and @data-field-name='%s' and @data-parent-tile-key='%s']" % (random_field_name_key, tile_key), True)
	go_to_contacts_page()
	select_random_contact_from_contacts_page()
	random_contact_url = driver.execute_script('return window.location.href')
	test_element_present('Check for "%s" bidirectional connection type field presence in "%s" tile' % (random_field_name, tile_key), "//div[@id='%s_connection']" % random_field_name_key, True)
	random_record_name = random_string(10) + ' Record'
	test_click('Adding new connection element', "//div[@id='%s_connection']//button[contains(@class,'create-new-record')]" % random_field_name_key , True)
	test_click('Clicking the Name input field', "//form[contains(@class,'js-create-record')]/input", True)
	test_send_keys('Adding random record name "%s"' % random_record_name, "//div[@id='create-record-modal']//input[@name='title']" , random_record_name, True)
	test_click('Clicking the "Create Record" button', "//form[contains(@class,'js-create-record')]//button[contains(@class,'js-create-record-button')]", True)
	test_click('Closing "Create Record" modal', "//div[@id='create-record-modal']//button[@class='close-button']", True)
	test_click('Clicking the record link assigned to the connection field', "//div[@id='%s_connection']//span[@class='typeahead__label']/a" % random_field_name_key, True)
	time.sleep(1)
	test_element_present("Check for random contact's presence in target connection field", "//div[@id='%s_connection']//span[@class='typeahead__label']/a[@href='%s']" % (random_field_name_key, random_contact_url[:-1]), True)
	#Go back to DT Customization screen and continue testing other fields
	driver.get(current_dt_customization_url)
	test_click('Click "%s" tile menu' % tile_key, "//div[@data-modal='edit-tile' and @data-key='%s']" % tile_key, True)
	test_click('Click "add new field" in "%s" tile' % tile_key, "//span[@data-parent-tile-key='%s']" % tile_key, True)
	time.sleep(1)
	Select(driver.find_element(By.XPATH, "//select[@name='new-field-type']")).select_by_value('connection')


def add_connection_field_same_post_type_non_bidirectional(tile_key):
	post_type = get_post_type_from_wp_admin()
	Select(driver.find_element(By.XPATH, "//select[@name='connection-target']")).select_by_value(post_type)
	current_dt_customization_url = driver.execute_script('return window.location.href;')
	print(bolded('   └ \nAdding "%s -> %s" connection (non bi-directional)' % (post_type, post_type)))
	random_field_name = random_string(10) + ' Field'
	random_field_name_key = random_field_name.lower().replace(' ', '_')
	test_send_keys('Add random new field name', "//input[@name='edit-tile-label']", random_field_name, True)
	test_checkbox_checked('Check that "bidirectional" checkbox is originally checked', "//input[@id='multidirectional_checkbox']", True)
	test_click('Uncheck the "bidirectional" checkbox', "//input[@id='multidirectional_checkbox']", True)
	input('press enter to continue')
	test_click('Adding "%s" bidirectional connection type field' % random_field_name, "//button[@id='js-add-field' and @data-tile-key='%s']" % tile_key, True)
	test_element_present('Check New "bidirectional connection" type field "%s" was added to "%s" tile' % (random_field_name, tile_key), "//div[@class='field-settings-table-field-name' and @data-field-name='%s' and @data-parent-tile-key='%s']" % (random_field_name_key, tile_key), True)
	go_to_contacts_page()
	select_random_contact_from_contacts_page()
	random_contact_url = driver.execute_script('return window.location.href')
	test_element_present('Check for "%s" non-bidirectional connection type field presence in "%s" tile' % (random_field_name, tile_key), "//div[@id='%s_connection']" % random_field_name_key, True)
	random_record_name = random_string(10) + ' Record'
	test_click('Adding new connection element', "//div[@id='%s_connection']//button[contains(@class,'create-new-record')]" % random_field_name_key , True)
	test_click('Clicking the Name input field', "//form[contains(@class,'js-create-record')]/input", True)
	test_send_keys('Adding random record name "%s"' % random_record_name, "//div[@id='create-record-modal']//input[@name='title']" , random_record_name, True)
	test_click('Clicking the "Create Record" button', "//form[contains(@class,'js-create-record')]//button[contains(@class,'js-create-record-button')]", True)
	test_click('Closing "Create Record" modal', "//div[@id='create-record-modal']//button[@class='close-button']", True)
	test_click('Clicking the record link assigned to the connection field', "//div[@id='%s_connection']//span[@class='typeahead__label']/a" % random_field_name_key, True)
	time.sleep(1)
	test_element_not_present("Check for random contact's lack of presence in target connection field", "//div[@id='%s_connection']//span[@class='typeahead__label']/a[@href='%s']" % (random_field_name_key, random_contact_url[:-1]), True)
	input('paused. press enter to continue')
	#Go back to DT Customization screen and continue testing other fields
	driver.get(current_dt_customization_url)
	test_click('Click "%s" tile menu' % tile_key, "//div[@data-modal='edit-tile' and @data-key='%s']" % tile_key, True)
	test_click('Click "add new field" in "%s" tile' % tile_key, "//span[@data-parent-tile-key='%s']" % tile_key, True)
	time.sleep(1)
	Select(driver.find_element(By.XPATH, "//select[@name='new-field-type']")).select_by_value('connection')
	

def add_connection_field_different_post_type(tile_key, target_post_type):
	post_type = get_post_type_from_wp_admin()
	Select(driver.find_element(By.XPATH, "//select[@name='connection-target']")).select_by_value(post_type)
	current_dt_customization_url = driver.execute_script('return window.location.href;')
	print(bolded('   └ \nAdding "%s -> %s" connection' % (post_type, target_post_type)))
	print('add_connection_field_different_post_type: %s -> %s' % (post_type, target_post_type))
	input('press enter to continue')
	random_field_name = random_string(10) + ' Field'
	random_field_name_key = random_field_name.lower().replace(' ', '_')
	test_send_keys('Add random new field name', "//input[@name='edit-tile-label']", random_field_name, True)
	test_checkbox_checked('Check that "bidirectional" checkbox is checked', "//input[@id='multidirectional_checkbox']", True)
	test_click('Adding "%s" bidirectional connection type field' % random_field_name, "//button[@id='js-add-field' and @data-tile-key='%s']" % tile_key, True)
	test_element_present('Check New "bidirectional connection" type field "%s" was added to "%s" tile' % (random_field_name, tile_key), "//div[@class='field-settings-table-field-name' and @data-field-name='%s' and @data-parent-tile-key='%s']" % (random_field_name_key, tile_key), True)
	go_to_contacts_page()
	select_random_contact_from_contacts_page()
	random_contact_url = driver.execute_script('return window.location.href')
	test_element_present('Check for "%s" bidirectional connection type field presence in "%s" tile' % (random_field_name, tile_key), "//div[@id='%s_connection']" % random_field_name_key, True)
	test_click('Assigning bidirectional connection to the first contact on the typeahead', "//input[contains(@class,'js-typeahead-%s')]" % random_field_name_key , True)
	test_click('Clicking the first element on the list', "//li[@class='typeahead__item typeahead__group-contacts' and @data-index=0]", True)
	test_click('Clicking the contact link assigned to the connection field', "//div[@id='%s_connection']//span[@class='typeahead__label']/a" % random_field_name_key, True)
	test_element_attribute_matches("Checking if new record is present and matches target connection field", "//div[@id='%s_connection']//span[@class='typeahead__label']/a" % random_field_name_key, 'href', random_contact_url, True)
	#Go back to DT Customization screen and continue testing other fields
	driver.get(current_dt_customization_url)
	test_click('Click "%s" tile menu' % tile_key, "//div[@data-modal='edit-tile' and @data-key='%s']" % tile_key, True)
	test_click('Click "add new field" in "%s" tile' % tile_key, "//span[@data-parent-tile-key='%s']" % tile_key, True)
	time.sleep(1)
	Select(driver.find_element(By.XPATH, "//select[@name='new-field-type']")).select_by_value('connection')


def test_adding_all_collapsable_field_types_for_all_tiles():
	tile_keys = get_all_tile_keys()
	for tk in tile_keys:
		print()
		print(bolded('Create all collapsable field types for "%s" tile') % tk)
		test_click('Click "%s" tile menu' % tk, "//div[@data-modal='edit-tile' and @data-key='%s']" % tk, True)
		all_field_type_values_collapsable = ['key_select', 'multi_select']
		for aftvc in all_field_type_values_collapsable:
			print(bolded('   └ Starting "%s" field type (collapsable)' % aftvc), True)
			random_field_name = random_string(10) + ' Field'
			random_field_name_key = random_field_name.lower().replace(' ', '_')
			test_click('Click "add new field" in "%s" tile' % tk, "//span[@data-parent-tile-key='%s']" % tk, True)
			test_send_keys('Add random new field name', "//input[@name='edit-tile-label']", random_field_name, True)
			time.sleep(1)
			Select(driver.find_element(By.XPATH, "//select[@name='new-field-type']")).select_by_value(aftvc)
			test_click('Adding "%s" %s type field' % (random_field_name, aftvc), "//button[@id='js-add-field' and @data-tile-key='%s']" % tk, True)
			test_element_present('Check New "%s" type field "%s" was added to "%s" tile' % (aftvc, random_field_name, tk), "//div[@class='field-settings-table-field-name expandable' and @data-field-name='%s' and @data-parent-tile-key='%s']" % (random_field_name_key, tk), True)
		delete_dt_field_customizations()
		refresh_page()
	print()

def test_adding_all_non_collapsable_field_types_for_all_tiles():
	tile_keys = get_all_tile_keys()
	for tk in tile_keys:
		print()
		print(bolded('Create all non-collapsable field types for "%s" tile') % tk)
		test_click('Click "%s" tile menu' % tk, "//div[@data-modal='edit-tile' and @data-key='%s']" % tk, True)
		all_field_type_values_non_collapsable = ['connection', 'tags', 'text', 'textarea', 'number', 'link', 'date']
		for aftvnc in all_field_type_values_non_collapsable:
			print(bolded('   └ Starting "%s" field type (non-collapsable)' % aftvnc), True)
			if aftvnc != 'connection':
				random_field_name = random_string(10) + ' Field'
				random_field_name_key = random_field_name.lower().replace(' ', '_')
				test_click('Click "add new field" in "%s" tile' % tk, "//span[@data-parent-tile-key='%s']" % tk, True)
				test_send_keys('Add random new field name', "//input[@name='edit-tile-label']", random_field_name, True)
				Select(driver.find_element(By.XPATH, "//select[@name='new-field-type']")).select_by_value(aftvnc)
				test_click('Adding "%s" %s type field' % (random_field_name, aftvnc), "//button[@id='js-add-field' and @data-tile-key='%s']" % tk, True)
				test_element_present('Check New "%s" type field "%s" was added to "%s" tile' % (aftvnc, random_field_name, tk), "//div[@class='field-settings-table-field-name' and @data-field-name='%s' and @data-parent-tile-key='%s']" % (random_field_name_key, tk), True)
			else:
				add_connection_fields(tk)
		delete_dt_field_customizations()
		test_click('Close "%s" tile menu in order to avoid viewport scroll issues' % tk, "//div[@data-modal='edit-tile' and @data-key='%s']" % tk, True)
	print()


#Load config
def load_config():
	try:
		open('config.ini', 'r')
		config = configparser.ConfigParser()
		config.read('config.ini')
		return config
	except:
		config = configparser.ConfigParser()

		config['DATABASE'] = {}
		database = config['DATABASE']
		database['host'] = 'localhost'
		database['user'] = 'root'
		database['password'] = 'root'
		database['unix_socket'] = '/Users/dariomanoukian/Library/Application Support/Local/run/ofqsoC0Xr/mysql/mysqld.sock'
		database['database_name'] = 'local'

		config['DEFAULT'] = {}
		config['DEFAULT']['longest_output'] = '0'

		with open('config.ini', 'w') as configfile:
			config.write(configfile)
		return config


config = load_config()
longest_output = int(config.get('DEFAULT', 'longest_output'))

chrome_options = Options()
chrome_options.add_experimental_option("detach", True)
chrome_options.add_argument("--log-level=3");
driver = webdriver.Chrome(ChromeDriverManager().install(), chrome_options=chrome_options)
driver.implicitly_wait(5)
driver.set_window_position(111, 1075, windowHandle='current')
driver.set_window_size(1440, 875, windowHandle='current')

os.system('clear')
print('Executing script\n\n')
#input('Press ENTER key to start...')


go_to_dt_customizations_page()
login('admin', 'admin')
delete_dt_field_customizations()
test_click('Click on "contacts" post_type button', "//div[@id='post-type-buttons']/a[contains(@href,'post_type=contacts')]")
test_click('Click post_type "Tiles" tab', "//a[contains(@class, 'nav-tab')][2]")
#test_adding_all_collapsable_field_types_for_all_tiles()
test_adding_all_non_collapsable_field_types_for_all_tiles()

# test_add_tile()
# test_add_field_to_tile('non-expandable')
# driver.quit()