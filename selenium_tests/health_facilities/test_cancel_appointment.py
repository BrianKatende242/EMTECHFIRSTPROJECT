#!/usr/bin/env python3
"""
Test script to verify cancel functionality for awaiting_payment appointments
"""
import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoSuchElementException

def test_cancel_awaiting_payment_appointment():
    """Test cancelling an appointment that is awaiting payment"""

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

        # Navigate to health facility dashboard
        driver.get('http://localhost:8000/health-facility/dashboard/1')
        print("Navigated to health facility dashboard")
        time.sleep(3)  # Pause to see the page load

        # Navigate to appointments page (book-doctor page)
        driver.get('http://localhost:8000/health-facility/1/book-doctor')
        print("Navigated to appointments page")
        time.sleep(3)  # Pause to see the appointments page

        # Wait for page to load
        wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, '.table')))

        # Find appointments table
        table = driver.find_element(By.CSS_SELECTOR, '.table')

        # Look for awaiting payment appointments with cancel button
        cancel_buttons = driver.find_elements(By.CSS_SELECTOR, 'button.cancel-appointment')

        if not cancel_buttons:
            print("No cancel buttons found - no awaiting_payment appointments to test")
            return False

        print(f"Found {len(cancel_buttons)} cancel button(s)")

        # Click the first cancel button
        cancel_button = cancel_buttons[0]
        appointment_id = cancel_button.get_attribute('data-appointment-id')
        print(f"Clicking cancel button for appointment ID: {appointment_id}")
        time.sleep(2)  # Pause before clicking
        cancel_button.click()

        # Wait for cancel modal to appear
        wait.until(EC.visibility_of_element_located((By.ID, 'cancelAppointmentModal')))
        time.sleep(2)  # Pause to see the modal

        # Verify modal content
        modal = driver.find_element(By.ID, 'cancelAppointmentModal')
        modal_title = modal.find_element(By.CLASS_NAME, 'modal-title').text
        print(f"Modal title: {modal_title}")

        # Click the cancel appointment button in modal
        cancel_confirm_button = modal.find_element(By.CSS_SELECTOR, 'button.btn-warning')
        print("About to click cancel appointment button...")
        time.sleep(2)  # Pause before confirming
        cancel_confirm_button.click()

        print("Clicked cancel appointment button")
        time.sleep(2)  # Pause after clicking

        # Wait for page to refresh after successful cancellation
        print("Waiting for page refresh...")
        time.sleep(3)  # Give time for page refresh

        # After page refresh, check if appointment status changed
        try:
            # Re-find the appointment row (page may have reloaded)
            cancel_buttons_after = driver.find_elements(By.CSS_SELECTOR, 'button.cancel-appointment')
            delete_buttons = driver.find_elements(By.CSS_SELECTOR, 'button.delete-appointment')
            
            # If we have delete buttons and fewer/no cancel buttons, cancellation worked
            if len(delete_buttons) > 0 and len(cancel_buttons_after) < len(cancel_buttons):
                print("✓ Page refreshed and appointment status changed to Cancelled")
                return True
            else:
                # Check for success message on refreshed page
                try:
                    alert = driver.find_element(By.CSS_SELECTOR, '.alert-success, .alert-warning')
                    if 'cancelled successfully' in alert.text.lower():
                        print("✓ Success message found after page refresh")
                        return True
                except:
                    pass
                
                print("✗ Appointment status not changed after page refresh")
                return False
                
        except Exception as e:
            print(f"✗ Error checking status after refresh: {e}")
            return False
        time.sleep(2)  # Pause after clicking

        # Wait for success message or modal to close
        try:
            # Wait for alert message
            wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, '.alert-success, .alert-warning')))
            alert = driver.find_element(By.CSS_SELECTOR, '.alert-success, .alert-warning')
            print(f"Success message: {alert.text}")
            time.sleep(3)  # Pause to see the success message

            # Check if appointment status changed to cancelled
            time.sleep(1)  # Give time for DOM update

            # Find the appointment row and check status
            appointment_row = driver.find_element(By.CSS_SELECTOR, f'button[data-appointment-id="{appointment_id}"]').find_element(By.XPATH, '../..')
            status_cell = appointment_row.find_elements(By.TAG_NAME, 'td')[4]  # Status column
            status_badge = status_cell.find_element(By.CLASS_NAME, 'badge')

            if 'Cancelled' in status_badge.text:
                print("✓ Appointment status successfully changed to Cancelled")
                print("✓ Cancel functionality working correctly")
                return True
            else:
                print(f"✗ Appointment status not changed. Current status: {status_badge.text}")
                return False

        except TimeoutException:
            print("✗ No success message appeared")
            return False

    except Exception as e:
        print(f"✗ Test failed with error: {str(e)}")
        return False

    finally:
        if driver:
            driver.quit()
            print("Driver closed")

if __name__ == "__main__":
    print("Testing cancel functionality for awaiting_payment appointments...")
    success = test_cancel_awaiting_payment_appointment()
    if success:
        print("\n✓ Test passed: Cancel functionality works correctly")
    else:
        print("\n✗ Test failed: Cancel functionality not working")