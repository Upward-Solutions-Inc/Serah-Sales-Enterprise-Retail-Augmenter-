# Hr Management System Blueprint

Daily Time Record  
├── Clock In/Out  
├── Overtime  (request to HR/admin)
├── Time Logs  
├── Configuration
 
Note: Grace Period (5mins for each clock In in morning & afternoon shift)

-> General Case
Grace Period | Morning shift In | Morning shift Out
Grace Period | Afternoon shift In | Afternoon shift Out

Surplus Time Considered As Overtime (Morning & Afternoon shift) default is 30mins above

-> Specific

-> Speacial Case
Night shift In
Break Time
Nigt Shift Out

Holiday dates?


Payroll  
├── Payslip  
├── Salary Computation  (2 Tabs [ Basic salary & Deduction ])
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

30mins > OT = !OT
30mins < OT = OT

DEDUCTIONS
✔ Income Tax (get the online matrix)
✔SSS (4.5%)
✔PhilHealth (2.5%)
✔Pagibig ( Default - 100 | 1,500 > MS= 1%, |  1,500 < MS= 2%),
✔Loan
✔Late



├── Payroll Reports 