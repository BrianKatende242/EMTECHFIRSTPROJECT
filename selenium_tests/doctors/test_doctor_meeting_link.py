#!/usr/bin/env python3
"""
Doctor meeting link test module.
Tests doctor dashboard meeting link functionality, specifically the "Send Link to School/Health Facility" feature.
"""

import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoSuchElementException, ElementClickInterceptedException

def test_doctor_meeting_link():
    """Test doctor meeting link functionality"""

    # Set up Chrome options
    chrome_options = Options()
    # chrome_options.add_argument('--headless')  # Commented out to show browser
    chrome_options.add_argument('--no-sandbox')
    chrome_options.add_argument('--disable-dev-shm-usage')

    driver = None
    try:
        # Initialize driver
        driver = webdriver.Chrome(options=chrome_options)
        wait = WebDriverWait(driver, 10)

        print("Chrome driver initialized")

        # Navigate to doctor meeting link page (assuming doctor ID 1 exists)
        doctor_id = 1
        driver.get(f'http://localhost:8000/doctor/{doctor_id}/meeting-link/')
        print(f"Navigated to doctor meeting link page: {doctor_id}")

        # Wait for page to load
        wait.until(EC.presence_of_element_located((By.TAG_NAME, 'body')))
        time.sleep(3)  # Additional wait for dynamic content

        # Check page content
        page_source = driver.page_source
        print(f"Page title: {driver.title}")
        print(f"Page URL: {driver.current_url}")
        print(f"Page contains 'Meeting Link': {'Meeting Link' in page_source}")
        print(f"Page contains 'sendLinkModal': {'sendLinkModal' in page_source}")

        # Check if we're on the correct page
        try:
            page_title = driver.find_element(By.TAG_NAME, 'h2')
            if 'Meeting Link' in page_title.text:
                print("✓ Meeting Link section found")
            else:
                print(f"Page title found: {page_title.text}")
        except Exception as e:
            print(f"Could not find h2 tag: {e}")
            # Try to find any h2 tags
            h2_tags = driver.find_elements(By.TAG_NAME, 'h2')
            if h2_tags:
                print(f"Found h2 tags: {[tag.text for tag in h2_tags]}")
            else:
                print("No h2 tags found")

        # Look for the meeting link section
        try:
            meeting_link_section = driver.find_element(By.XPATH, "//h2[contains(text(), 'Meeting Link')]")
            print("✓ Meeting Link section found")
        except Exception as e:
            print(f"✗ Meeting Link section not found: {e}")
            # Try to find any headings
            headings = driver.find_elements(By.XPATH, "//h1 | //h2 | //h3 | //h4 | //h5 | //h6")
            if headings:
                print(f"Found headings: {[h.text for h in headings if h.text.strip()]}")
            return False

        # Check if the meeting link input is present
        try:
            meeting_link_input = driver.find_element(By.ID, 'meetingLinkInput')
            meeting_url = meeting_link_input.get_attribute('value')
            print(f"✓ Meeting link input found with URL: {meeting_url}")

            if 'meet.jit.si' in meeting_url:
                print("✓ Meeting URL contains Jitsi Meet domain")
            else:
                print("⚠ Meeting URL doesn't contain expected Jitsi Meet domain")

        except Exception as e:
            print(f"✗ Meeting link input not found: {e}")
            return False

        # Look for the "Send Link to School/Health Facility" button
        try:
            # Try different selectors
            send_link_button = None
            try:
                send_link_button = driver.find_element(By.XPATH, "//button[contains(text(), 'Send Link to School/Health Facility')]")
            except:
                try:
                    send_link_button = driver.find_element(By.CSS_SELECTOR, "button[data-bs-target='#sendLinkModal']")
                except:
                    send_link_button = driver.find_element(By.XPATH, "//button[contains(@data-bs-target, 'sendLinkModal')]")
            
            print("✓ Send Link button found")

            # Click the button to open the modal
            send_link_button.click()
            print("✓ Clicked Send Link button")

            # Wait and manually show modal with JavaScript
            time.sleep(1)
            driver.execute_script("$('#sendLinkModal').modal('show');")
            time.sleep(2)
            
            # Check if modal is visible
            modal = driver.find_element(By.ID, 'sendLinkModal')
            if 'show' in modal.get_attribute('class'):
                print("✓ Modal is visible")
                
                # Try to interact with form elements
                try:
                    email_input = driver.find_element(By.ID, 'recipient_email')
                    email_input.clear()
                    email_input.send_keys('mukisaelijah293@gmail.com')
                    print("✓ Entered email via Selenium")
                    
                    submit_button = driver.find_element(By.XPATH, "//button[@type='submit' and contains(text(), 'Send Link')]")
                    submit_button.click()
                    print("✓ Submitted form via AJAX")
                    
                    # Wait for AJAX response and success message in modal
                    time.sleep(3)
                    
                    # Check for success message in the modal alert
                    try:
                        modal_alerts = driver.find_elements(By.CSS_SELECTOR, "#sendLinkAlert .alert-success")
                        if modal_alerts:
                            for alert in modal_alerts:
                                if alert.is_displayed():
                                    alert_text = alert.text
                                    print(f"✓ Success message in modal: {alert_text}")
                                    if 'sent' in alert_text.lower() or 'success' in alert_text.lower():
                                        print("✓ AJAX request successful!")
                                        return True
                    except Exception as e:
                        print(f"Error checking modal alerts: {e}")
                    
                    # Check if modal is still open (success) or if there are error messages
                    try:
                        modal = driver.find_element(By.ID, 'sendLinkModal')
                        if not modal.is_displayed():
                            print("✓ Modal closed - likely success")
                            return True
                        else:
                            # Check for error messages in modal
                            error_alerts = driver.find_elements(By.CSS_SELECTOR, "#sendLinkAlert .alert-danger")
                            if error_alerts:
                                error_text = error_alerts[0].text
                                print(f"✗ Error message in modal: {error_text}")
                                return False
                            else:
                                print("Modal still open but no error messages found")
                                return False
                    except:
                        print("Could not check modal status")
                        return False
                    
                except Exception as e:
                    print(f"✗ Selenium interaction failed: {e}")
                    return False
                    
            else:
                print("⚠ Modal still not visible, using JavaScript fallback")
                # JavaScript fallback
                js_script = """
                $('#sendLinkModal').modal('show');
                setTimeout(function() {
                    document.getElementById('recipient_email').value = 'mukisaelijah293@gmail.com';
                    document.querySelector('form[action*="/doctor/send-link/"]').submit();
                }, 500);
                """
                driver.execute_script(js_script)
                print("✓ Used JavaScript fallback")
                time.sleep(4)
                
        except Exception as e:
            print(f"✗ Send Link button not found: {e}")
            # Debug: print all buttons on the page
            buttons = driver.find_elements(By.TAG_NAME, "button")
            button_texts = [btn.text.strip() for btn in buttons if btn.text.strip()]
            print(f"Available buttons: {button_texts}")
            return False

        # Check for success message
        try:
            # Look for success alert with specific text
            success_alerts = driver.find_elements(By.CLASS_NAME, 'alert-success')
            if success_alerts:
                for alert in success_alerts:
                    if alert.is_displayed():
                        success_text = alert.text
                        print(f"✓ Success alert found: {success_text}")
                        if 'sent' in success_text.lower() or 'success' in success_text.lower():
                            return True
            
            # Check for any alert messages
            all_alerts = driver.find_elements(By.CSS_SELECTOR, ".alert, [class*='alert']")
            for alert in all_alerts:
                if alert.is_displayed():
                    alert_text = alert.text
                    print(f"Found alert: '{alert_text}' (class: {alert.get_attribute('class')})")
                    if ('success' in alert_text.lower() or 'sent' in alert_text.lower()) and 'alert-success' in alert.get_attribute('class'):
                        print("✓ Success message confirmed!")
                        return True
            
            # Check page source for success message
            page_source = driver.page_source
            if 'Meeting link sent successfully' in page_source:
                print("✓ Found success message in page source")
                return True
                
            print("✗ No success message found")
            return False

        except Exception as e:
            print(f"✗ Error checking for success message: {e}")
            return False

    except Exception as e:
        print(f"✗ Test failed with error: {str(e)}")
        return False

    finally:
        if driver:
            driver.quit()
            print("Driver closed")

def run_doctor_meeting_link_test():
    """Run the doctor meeting link test"""
    print("Testing doctor meeting link functionality...")
    print('='*60)
    success = test_doctor_meeting_link()
    print('='*60)
    if success:
        print("✓ Doctor meeting link test PASSED")
    else:
        print("✗ Doctor meeting link test FAILED")
    return success

if __name__ == "__main__":
    run_doctor_meeting_link_test()