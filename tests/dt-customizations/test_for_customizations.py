from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select
from webdriver_manager.chrome import ChromeDriverManager
from sty import fg, bg, ef, rs
import os
import random
import string
import time

chrome_options = Options()
chrome_options.add_experimental_option("detach", True)
chrome_options.add_argument("--log-level=3");
driver = webdriver.Chrome(ChromeDriverManager().install(), chrome_options=chrome_options)


os.system('clear')
print('Executing script\n\n')
#input('Press any key to start...')

hostname = 'http://localhost:10089'
url = '%s/wp-admin/admin.php?page=dt_customizations' % hostname
driver.get(url)


def wait_until_load():
	global driver
	WebDriverWait(driver, 10).until(lambda driver: driver.execute_script('return document.readyState') == 'complete')


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


def test_click(message, xpath, indent=False):
	global driver
	wait_until_load()
	indentation = ' - '
	if indent == True:
		indentation = '   └ '
	print('%s%s' % (indentation, message) , end='')
	try:
		driver.find_element(By.XPATH, xpath).click()
		test_passed()
	except:
		test_not_passed()

def test_send_keys(message, xpath, keys, indent=False):
	global driver
	wait_until_load()
	indentation = ' - '
	if indent == True:
		indentation = '   └ '
	print('%s%s' % (indentation, message) , end='')
	try:
		driver.find_element(By.XPATH, xpath).send_keys(keys)
		test_passed()
	except:
		print(xpath)
		test_not_passed()

def test_element_presence(message, xpath, indent=False):
	global driver
	wait_until_load()
	indentation = '- '
	if indent == True:
		indentation = '   └ '
	print('%s%s' % (indentation, message), end='')
	try:
		time.sleep(1)
		driver.find_element(By.XPATH, xpath)
		test_passed()
	except:
		test_not_passed()

def test_add_field_tileless(field_type=''):
	print(' - Add non-expandable new field to tile-less post type', end='')
	if field_type not in ['expandable', 'non-expandable']:
		test_not_passed("field type must be 'expandable' or 'non-expandable'\n")
		exit()
	global driver
	test_click('Click "add new field button"', "//span[contains(@class, 'add-new-field')]", True)
	test_element_presence('Check Tile label = "This post type doesn\'t have any tiles"', '''//table[@class="modal-overlay-content-table"]/tr/td/i[.="This post type doesn't have any tiles"]''', True)
	time.sleep(1)
	random_field_name = random_string(10) + ' Field'
	random_field_name_key = random_field_name.lower().replace(' ', '_')
	test_send_keys('Add random New Field Name', "//input[@id='new-field-name-null']", random_field_name, True)
	
	if field_type == 'non-expandable':
		field_type_value = 'text'
		xpath_new_field_added = "//div[@class='field-settings-table-field-name' and contains(., '%s')]" % random_field_name
		xpath_new_field_added_data_parent_tile_key = "//div[@class='field-settings-table-field-name' and @data-field-name='%s' and @data-parent-tile-key='null']" % random_field_name_key
	
	if field_type == 'expandable':
		field_type_value = 'key_select'
		xpath_new_field_added = "//div[@class='field-settings-table-field-name expandable' and contains(., '%s')]" % random_field_name
		xpath_new_field_added_data_parent_tile_key = "//div[@class='field-settings-table-field-name expandable' and @data-field-name='%s' and @data-parent-tile-key='null']" % random_field_name_key
	
	Select(driver.find_element(By.XPATH, "//select[@id='new-field-type-null']")).select_by_value(field_type_value)
	test_click("Click 'Save' button", "//button[@id='js-add-field']", True)
	test_element_presence("Check New Field '%s' was added to menu" % random_field_name, xpath_new_field_added, True)
	test_element_presence("Check New Field has data-parent-tile-key = 'null'", xpath_new_field_added_data_parent_tile_key, True )

login('admin', 'admin')
test_click('Click on "peoplegroups" post_type button', "//div[@id='post-type-buttons']/a[contains(@href,'post_type=peoplegroups')]")
test_click('Click post_type "Tiles" tab', "//a[contains(@class, 'nav-tab')][2]")
test_add_field_tileless('non-expandable')
test_add_field_tileless('expandable')
driver.quit()



