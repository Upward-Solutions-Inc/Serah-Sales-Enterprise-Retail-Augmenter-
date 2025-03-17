********* Hr Management System Blueprint  *********

# Modules & UI
Daily Time Record  
├── Time Clock ✔️
├── Schedule (Regular & Night) ✔️
├── Overtime  (request to HR/admin) ~
├── Time Logs ~

Payroll  
├── Payslip ✔️
├── Payroll Reports ✔️
├── Salary Computation ✔️

# Database Table
dtr_logs
dtr_config

# DTR Schedule
- General Case
● TIME VARIABLES
Grace Period | Morning shift In | Morning shift Out
Grace Period | Afternoon shift In | Afternoon shift Out
Overtime Threshold (Morning & Afternoon shift) default is 30mins above

-- save the data to the dtr_config DB -- 
> Note: Grace Period (5mins for each clock In in morning & afternoon shift)
> Note Overtime: 30mins > OT = !OT
>                30mins < OT = OT


-  Specific Case
Night shift In
Break Time
Nigt Shift Out

Holiday dates?
> Idea: Create a Year Timeline similar to the Github contribution each box should be clickable
>       and show attributes. Then each employe has that Yearly Salary Timeline



# Table Display #

# Clock In/Out
Employee | Date | Shift | Clock In | Clock Out | Late | Overtime | Total Work Hours

- FUNCTION for Clock IN -
 Get the logged-in employee information.  
 Get the current date and time.  
 Retrieve the shift start times from the dtr_config table.  
 Determine which shift the clock-in time belongs to:  
    If between morning shift start and before afternoon shift, assign Morning Shift.  
    If between afternoon shift start and before night shift, assign Afternoon Shift.  
    Otherwise, assign Night Shift.  
 Calculate the late minutes:  
    If clock-in time is later than the shift start time, subtract to get late minutes.  
    Otherwise, set late minutes to 0.  
 Save the clock-in details, including employee name, date, shift, clock-in time, and late minutes.  

- FUNCTION for Clock Out
 Get the logged-in employee information.  
 Get the current date and time.  
 Retrieve the employee's clock-in record for the current date.  
 If no clock-in record exists, return an error message.  
 Retrieve the shift start time and standard work hours from the dtr_config table.  
 Calculate the total work hours by subtracting clock-in time from clock-out time.  
 Determine overtime:  
    If total work hours exceed standard work hours, calculate overtime.  
    Otherwise, set overtime to 0.  
 Update the employee’s attendance record with the clock-out time, total work hours, and overtime.  



# Payrool


# Salary Computation
Dropdown - List of all users/employee (will need to select for their specific)
● BASIC PAY ●
✔ Pay Rate______per month ...Regular Workday (1.0x) + btn CALCUlATE
-         ______ per 15 days
-         ______ per week
-         ______ per days
-         ______ per hour
✔ Standard Working Hours - 8 hours

● ADDITIONAL PAY ●
✔ Regular Overtime Pay (1.25x Rate)
✔ Allowance
✔ Performance Bonus
✔ Incentives
✔ Christmas Bonus
✔ 13th Month Pay (Mandatory by Law) 

● DEDUCTIONS ●
✔ Income Tax (get the online matrix) then show the bracket
✔ SSS (4.5%) of MS
✔ PhilHealth (2.5%) of MS
✔ Pagibig ( Default - 100 | 1,500 > MS= 1%, |  1,500 < MS= 2%),
✔ Loan
✔ Late







# Salaey Computation Matrix
GROSS PAY
- Basic Pay
✔ Regular Workday (1.0x)
✔ Regular Holiday (2.0x)

- Overtime
✔ Regular Overtime (1.25x Rate)
✔ Rest Day Overtime (1.69x Rate)
✔ Holiday Overtime (2.6x Rate)

- Bonus
✔ Performance Bonus
✔ Incentives
✔ Christmas Bonus
✔ 13th Month Pay (Mandatory by Law) 

DEDUCTIONS
✔ Income Tax (get the online matrix)
✔SSS (4.5%)
✔PhilHealth (2.5%)
✔Pagibig ( Default - 100 | 1,500 > MS= 1%, |  1,500 < MS= 2%),
✔Loan
✔Late
|

# use -- User.php -- to get the current log user