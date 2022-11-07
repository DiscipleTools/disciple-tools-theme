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
import mysql.connector

hostname = 'http://localhost:10089'

def get_my_db():
	my_db = mysql.connector.connect(
	  host = 'localhost',
	  user = 'root',
	  password = 'root',
	  unix_socket = '/Users/dariomanoukian/Library/Application Support/Local/run/ofqsoC0Xr/mysql/mysqld.sock',
	  database = 'local'
	)
	return my_db

def get_db_prefix():
	db_prefix = 'wp_'
	return db_prefix

chrome_options = Options()
chrome_options.add_experimental_option("detach", True)
chrome_options.add_argument("--log-level=3");
driver = webdriver.Chrome(ChromeDriverManager().install(), chrome_options=chrome_options)
driver.implicitly_wait(5)

os.system('clear')
print('Executing script\n\n')
#input('Press ENTER key to start...')

longest_output = 0

def calculate_longest_output(message):
	global longest_output
	if len(message) > longest_output:
		longest_output = len(message)

def get_space_chars(message):
	global longest_output
	space_chars = 0
	if len(message) < longest_output:
		space_chars = longest_output - len(message)
	return space_chars

def bolded(message):
	return '\033[1m%s\033[0m' % message

url = '%s/wp-admin/admin.php?page=dt_customizations' % hostname
driver.get(url)


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
	print(' - Log in', end='')
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
	my_db = get_my_db()
	my_cursor = my_db.cursor()
	db_prefix = get_db_prefix()
	my_cursor.execute("DELETE FROM `%soptions` WHERE option_name = 'dt_field_customizations';" % db_prefix)
	my_db.commit()
	print('*** DT Field Customizations deleted successfully ***')

### DATABASE FUNCTIONS - END ###	


def test_click(message, xpath, indent=False):
	global driver
	send_message(message, indent)
	try:
		WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.XPATH, xpath)))
		driver.find_element(By.XPATH, xpath).click()
		test_passed()
	except:
		test_not_passed()

def test_click_random_from(message, xpath, indent=False):
	global driver
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
	global driver
	wait_until_load()
	send_message(message, indent)
	try:
		driver.find_element(By.XPATH, xpath).send_keys(keys)
		test_passed()
	except:
		print(xpath)
		test_not_passed()

def test_element_presence(message, xpath, indent=False):
	global driver
	send_message(message, indent)
	try:
		time.sleep(1)
		driver.find_element(By.XPATH, xpath)
		test_passed()
	except:
		test_not_passed()

def test_add_tile():
	print(bolded('\n - Add tile to post type'))
	test_click('Click "add new tile" link', "//a[@id='add-new-tile-link']" , True)
	random_tile_name = random_string(10) + ' Tile'
	random_tile_name_key = random_tile_name.lower().replace(' ', '_')
	test_send_keys('Add random New Tile Name', "//input[@id='new_tile_name']", random_tile_name, True)
	test_click('Click "Create Tile" button', "//button[@id='js-add-tile']", True)
	test_element_presence("Check New Tile '%s' was added to menu" % random_tile_name, "//div[@class='field-settings-table-tile-name expandable' and @data-key='%s']" % random_tile_name_key, True)
	go_to_contacts_page()
	select_random_contact_from_contacts_page()
	test_element_presence("Check '%s' tile is present on post type page" % random_tile_name, "//div[@id='%s-tile']" % random_tile_name_key, True)

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
		test_element_presence('Test tile presence in page', 'xpath for tile here', True)
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
			Select(driver.find_element(By.XPATH, "//select[@name='new-field-type']")).select_by_value(aftvc)
			test_click('Adding "%s" %s type field' % (random_field_name, aftvc), "//button[@id='js-add-field' and @data-tile-key='%s']" % tk, True)
			test_element_presence('Check New "%s" type field "%s" was added to "%s" tile' % (aftvc, random_field_name, tk), "//div[@class='field-settings-table-field-name expandable' and @data-field-name='%s' and @data-parent-tile-key='%s']" % (random_field_name_key, tk), True)
		delete_dt_field_customizations()
		refresh_page()
	print()

def test_adding_all_non_collapsable_field_types_for_all_tiles():
	tile_keys = get_all_tile_keys()
	for tk in tile_keys:
		print()
		print(bolded('Create all non-collapsable field types for "%s" tile') % tk)
		all_field_type_values_non_collapsable = ['tags', 'text', 'textarea', 'number', 'link', 'date']
		for aftvnc in all_field_type_values_non_collapsable:
			print(bolded('   └ Starting "%s" field type (non-collapsable)' % aftvnc), True)
			random_field_name = random_string(10) + ' Field'
			random_field_name_key = random_field_name.lower().replace(' ', '_')
			test_click('Click "add new field" in "%s" tile' % tk, "//span[@data-parent-tile-key='%s']" % tk, True)
			test_send_keys('Add random new field name', "//input[@name='edit-tile-label']", random_field_name, True)
			Select(driver.find_element(By.XPATH, "//select[@name='new-field-type']")).select_by_value(aftvnc)
			test_click('Adding "%s" %s type field' % (random_field_name, aftvnc), "//button[@id='js-add-field' and @data-tile-key='%s']" % tk, True)
			test_element_presence('Check New "%s" type field "%s" was added to "%s" tile' % (aftvnc, random_field_name, tk), "//div[@class='field-settings-table-field-name' and @data-field-name='%s' and @data-parent-tile-key='%s']" % (random_field_name_key, tk), True)
		delete_dt_field_customizations()
	test_click('Close "%s" tile menu in order to avoid viewport scroll issues' % tk, "//div[@data-modal='edit-tile' and @data-key='%s']" % tk, True)
	print()

login('admin', 'admin')
test_click('Click on "contacts" post_type button', "//div[@id='post-type-buttons']/a[contains(@href,'post_type=contacts')]")
test_click('Click post_type "Tiles" tab', "//a[contains(@class, 'nav-tab')][2]")
test_adding_all_collapsable_field_types_for_all_tiles()
test_adding_all_non_collapsable_field_types_for_all_tiles()

# test_add_tile()
# test_add_field_to_tile('non-expandable')
# driver.quit()