

-- This is just an example. This will be called in the 'read.php' file.

/*
 * Table: DAYCARE
 * Description: Selects all attributes from the table.
 * Input: the name of the daycare.
 */
DROP PROCEDURE IF EXISTS SelectDaycare;
CREATE PROCEDURE SelectDaycare (
      IN name VARCHAR(100)
    , IN address VARCHAR(100)
    )

BEGIN
    SELECT
         DaycareName
       , DaycareAddress
       , TotalNumOfCaretakers
    FROM DAYCARE
    WHERE DaycareName = name;
END;

/*
 * Table: DAYCARE
 * Description: Create a daycare
 */
DROP PROCEDURE IF EXISTS CreateDaycare;
CREATE PROCEDURE CreateDaycare (
      IN name VARCHAR(100)
    , IN address VARCHAR(100)
    , IN caretakers INT
    )

BEGIN
    INSERT INTO DAYCARE (DaycareName, DaycareAddress, TotalNumOfCaretakers)
    VALUES (name,address,caretakers);
END;


/* Table: CHILD
 * Method: GetMedicalCondition()
 * Description: Gets all the conditions and treatments a child may have
 * @param ChildSin - SIN of Child
 */
DROP PROCEDURE IF EXISTS GetMedicalCondition;
CREATE PROCEDURE GetMedicalCondition (
      IN childSIN VARCHAR(8)
    )

BEGIN
    SELECT
        chld.SIN
      , prsn.FirstName
      , prsn.LastName
      , cndtn.ConditionName
      , cndtn.ConditionTreatment
    FROM CHILD as chld
    INNER JOIN
        CONDITIONS as cndtn
        ON cndtn.ChildSIN = chld.SIN
    INNER JOIN
        PERSON as prsn
        ON prsn.SIN = chld.SIN
    WHERE chld.SIN = childSIN;
END;


/* Table: CHILD
 * Method: ChildGetDailyReport()
 * Description: Gets all daily reports for the child ordered by date (desc)
 * @param ChildSIN - Child SIN
 */
DROP PROCEDURE IF EXISTS ChildGetDailyReport;
CREATE PROCEDURE ChildGetDailyReport (
      IN childSIN VARCHAR(8)
    )

BEGIN
    SELECT
         chld.SIN
       , prsn.FirstName
       , prsn.LastName
       , dlyRprt.ReportId
       , dlyRprt.CaretakerEmployeeId
       , dlyRprt.ReportDate
       , dlyRprt.ReportComment
    FROM DAILY_REPORT as dlyRprt
    INNER JOIN
        CHILD as chld
        ON chld.SIN = dlyRprt.ChildSIN
    INNER JOIN
        PERSON as prsn
        ON prsn.SIN = chld.SIN
    WHERE chld.SIN = childSIN;
END;


/* Table: CHILD
 * Method: ChildGetRoom()
 * Description: Get the room for the child
 * @param ChildSIN - SIN of Child
 */
DROP PROCEDURE IF EXISTS ChildGetRoom;
CREATE PROCEDURE ChildGetRoom (
      IN childSIN VARCHAR(8)
    )

BEGIN
    SELECT
         chld.SIN
       , chld.RoomId
       , rm.DaycareName
       , rm.DaycareAddress
    FROM CHILD as chld
    INNER JOIN
         ROOM as rm
         ON rm.DaycareName = chld.DaycareName
            AND rm.DaycareAddress = chld.DaycareAddress
            AND chld.RoomId = rm.RoomId
    WHERE chld.SIN = childSIN;
END;


/* Table: PARENT_GUARDIAN
 * Method: SelectCaretaker()
 * Description: View all caretakers and their info the specified daycare
 * @param dycrName - Name of the daycare
 * @param dycrAddr - The address of the daycare
 */
DROP PROCEDURE IF EXISTS SelectCaretaker;
CREATE PROCEDURE SelectCaretaker (
      IN dycrName VARCHAR(100)
    , IN dycrAddr VARCHAR(100)
    )

BEGIN
    SELECT
         prsn.FirstName
       , prsn.LastName
       , prsn.Gender
       , emp.DaycareName
       , emp.DaycareAddress
       , prsn.StartDay
       , prsn.StartMonth
       , prsn.StartYear
       , crtkr.Availability
       , crtkrSpclztn.SpecializationType
    FROM CARETAKER as crtkr
    INNER JOIN
         PERSON as prsn
         ON prsn.SIN = crtkr.SIN
    INNER JOIN
         CARETAKER_SPECIALIZATION as crtkrSpclztn
         ON crtkrSpclztn.CaretakerSIN = crtkr.SIN
    INNER JOIN
         EMPLOYEE as emp
         ON emp.EmployeeId = crtkr.EmployeeId
    WHERE emp.DaycareName = dycrName
        AND emp.DaycareAddress = dycrAddr;
END;


/* Table: PARENT_GUARDIAN
 * Method: GetChild()
 * Description: Returns child(ren) of parents
 * @param ParentSIN 
 */
DROP PROCEDURE IF EXISTS GetChild;
CREATE PROCEDURE GetChild(
      IN prntSIN VARCHAR(8)
    )  

BEGIN
    SELECT 
         chld.SIN
       , prsn.FirstName
       , prsn.LastName
       , chld.DateOfBirth
    FROM CHILD as chld
    INNER JOIN
         PERSON as prsn
         ON prsn.SIN = chld.SIN
    WHERE chld.ParentSIN = prntSIN;
END;


/* Table: PARENT_GUARDIAN
 * Method: PayBill()
 * Description: Parent pays outstanding bill
 * @param id - id of the bill to be paid
 * @param PaymentMethod - payment method of parent_guardian
 * @param amountPending - the amount left over after payment
 */
DROP PROCEDURE IF EXISTS PayBill;
CREATE PROCEDURE PayBill(
      IN id INT
    , IN pmntMthd VARCHAR(30)
    , IN amntPndg DECIMAL(6,2)
    )
    
BEGIN
    UPDATE BILL
    SET PaymentMethod = pmntMthd,
        AmountPending = amntPndg
    WHERE BillId = id;
END;


/* Table: ADMIN
 * Method: AddToWaitlist()
 * Description: Add a new Child/family to the waitlist
 * @param chldnme - Name of child to be inserted
 * @param fmlyNme - Last name of child/family
 * @param empId - ID of employee submitting
 */
DROP PROCEDURE IF EXISTS AddToWaitlist;
CREATE PROCEDURE AddToWaitlist(
      IN chldFrstNme VARCHAR(30)
    , IN chldLstNme VARCHAR(30)
    , IN empId INT
    )
    
BEGIN
    INSERT INTO WAITLIST (ChildFirstName, ChildLastName, SubmittedById)
    VALUES (chldFrstNme, chldLstNme, empId);
END;


/* Table: ADMIN
 * Method: CreateBill()
 * Description: Add a new bill
 * @param bill - Id of bill
 * @param empId - ID of employee submitting
 * @param method - Payment method
 * @param amount - amount of bill
 */
DROP PROCEDURE IF EXISTS CreateBill;
CREATE PROCEDURE CreateBill(
      IN bill INT
    , IN empId INT
    , IN method VARCHAR(30)
    , IN amount DECIMAL(6,2)
    )

BEGIN 
    INSERT INTO BILL (BillId, CreatedById, PaymentMethod, AmountPending)
    VALUES (bill, empId, method, amount);
END;
 

/* Table: ADMIN
 * Method: AddEmployee()
 * Description: Add a new employee
 * @param daycare - Daycare Name
 * @param address - Daycare Address
 * @param empSIN - SIN of new EmployeeID
 * @param empId - Employee ID
 * @param wrkHrs - Hours available to work
 */
DROP PROCEDURE IF EXISTS AddEmployee;
CREATE PROCEDURE AddEmployee(
      IN daycare VARCHAR(100)
    , IN address VARCHAR(100) 
    , IN empSIN VARCHAR(8)
    , IN empId INT
    , IN wrkHrs DECIMAL(4,2)
    )

BEGIN 
    INSERT INTO EMPLOYEE (DaycareName, DaycareAddress, SIN, EmployeeId, WorkHours)
    VALUES (daycare, address, empSIN, empId, wrkHrs);
END;


/* Table: ADMIN
 * Method: RemoveEmployee()
 * Description: Remove an employee
 * @param EmployeeId - ID of employee to remove
 */
DROP PROCEDURE IF EXISTS RemoveEmployee;
CREATE PROCEDURE RemoveEmployee(
      IN empId INT
    )

BEGIN 
    DELETE FROM EMPLOYEE
    WHERE EmployeeId = empId; 
END;


/* Table: PERSON
 * Method: AddPerson()
 * Description: Add a new person
 * @param prsnSIN - SIN of new person
 * @param frstNm - First name
 * @param lstNm - Last name
 * @param gndr - Gender
 * @param untNum - Unit number
 * @param strt - Street address
 * @param cty - City
 * @param pstlCde - Postal code
 * @param strtDy - Start Day
 * @param strtMnth - StartMonth
 * @param strtYr - Start year
 * @param phneNm - Phone number
 */
DROP PROCEDURE IF EXISTS AddPerson;
CREATE PROCEDURE AddPerson(
      IN prsnSIN VARCHAR(8)
    , IN frstNm VARCHAR(30)
    , IN lstNm VARCHAR(30)
    , IN gndr VARCHAR(30)
    , IN untNum INT
    , IN strt VARCHAR(50)
    , IN cty VARCHAR(20)
    , IN pstlCode VARCHAR(20)
    , IN strtDy INT
    , IN strtMnth INT
    , IN strtYr INT
    , IN phnNum VARCHAR(20)
    )

BEGIN
    INSERT INTO PERSON (SIN, FirstName, LastName, Gender, AddrUnitNum, AddrStreet, AddrCity, AddrPostalCode, StartDay, StartMonth, StartYear)
    VALUES (prsnSIN, frstNm, lstNm, gndr, untNum, strt, cty, pstlCode, strtDy, strtMnth, strtYr);

    INSERT INTO PERSON_PHONE (SIN, PhoneNum)
    VALUES (prsnSIN, phnNum);
END;


/* Table: PERSON
 * Method: RemovePerson()
 * Description: Remove a person
 * @param prsnSIN - SIN of person to remove
 */
DROP PROCEDURE IF EXISTS RemovePerson;
CREATE PROCEDURE RemovePerson(
      IN prsnSIN VARCHAR(8)
    )

BEGIN
    DELETE FROM PERSON
    WHERE SIN = prsnSIN; 
END;


/* Table: EMPLOYEE
 * Method: GetDaycare()
 * Description: Gets the daycare the Employee works at
 * @param empId - Id of employee
 */
DROP PROCEDURE IF EXISTS GetDaycare;
CREATE PROCEDURE GetDaycare(
      IN empId INT
    )

BEGIN
    SELECT 
         DaycareName
       , DaycareAddress
    FROM EMPLOYEE
    WHERE EmployeeId = empId;
END;


/* Table: DAYCARE
 * Method: GetEmployees()
 * Description: Gets a list of all employees
 * @param dycreName - the daycarename
 * @param dycreAddress - the address of the daycre
 */
DROP PROCEDURE IF EXISTS GetEmployees;
CREATE PROCEDURE GetEmployees(
      IN dycreName VARCHAR(100)
    , IN dycreAddress VARCHAR(100)
    )

BEGIN 
    SELECT
         emp.SIN
       , emp.EmployeeId
       , prsn.FirstName
       , prsn.LastName
       , emp.WorkHours
    FROM EMPLOYEE as emp
    INNER JOIN
         PERSON as prsn
         ON prsn.SIN = emp.SIN
    WHERE emp.DaycareName = dycreName
        AND emp.DaycareAddress = dycreAddress;
END;


/* Table: DAYCARE
 * Method: DaycareGetRooms()
 * Description: Get a list of rooms at the daycare
 * @param dycreName - Name of the daycare
 * @param dycreAddress - the address of the daycare
 */
DROP PROCEDURE IF EXISTS DaycareGetRooms;
CREATE PROCEDURE DaycareGetRooms(
      IN dycreName VARCHAR(100)
    , IN dycreAddress VARCHAR(100)
    )

BEGIN
    SELECT
         RoomId
       , SeatsAvailable
    FROM ROOM
    WHERE DaycareName = dycreName
        AND DaycareAddress = dycreAddress;
END;


/* Table: CARETAKER
 * Method: AddReport()
 * Description: Insert report into Daily_report table
 * @param chldSIN - SIN of child the report is about
 * @param rptId - Id of the report
 * @param empId - EmployeeID of the caretaker inserting the report
 * @param rptDte - Date of the report
 * @param strtTme - Start time of the report
 * @param endtme - End time of the report
 * @param rptCmmnt - Text of report
 */
DROP PROCEDURE IF EXISTS AddReport;
CREATE PROCEDURE AddReport(
      IN chldSIN VARCHAR(8)
    , IN rptID INT
    , IN empId INT  
    , IN rptDte DATE
    , IN rptCmmnt VARCHAR(1000)
    )

BEGIN 
    INSERT INTO DAILY_REPORT (ChildSIN, ReportId, CaretakerEmployeeId, ReportDate, ReportComment)
    VALUES (chldSIN, rptId, empId, rptDte, rptCmmnt);
END;


/* Table: CARETAKER
 * Method: CaretakerGetDailyReport()
 * Description: Get daily report submitted by that caretaker
 * @param crtkrId - Id of caretaker
 * @param rptdate - Date wanted
 * @param chldSIN - SIN of child repoported on
 */
DROP PROCEDURE IF EXISTS CaretakerGetDailyReport;
CREATE PROCEDURE CaretakerGetDailyReport(
      IN  crtkrId INT  
    , IN  chldSIN VARCHAR(8)
    , IN  rptdate DATE
    )
BEGIN 
    SELECT
         ChildSIN
       , ReportComment
    FROM DAILY_REPORT
    WHERE ChildSIN = chldSIN
        AND CaretakerEmployeeId = crtkrId
        AND ReportDate = rptdate;
END;


/* Table: ROOM
 * Method: AssignChild()
 * Description: Assign a child to the room
 * @param chldSIN- SIN of CHILD
 * @param rmId - Id of Room being assigned
 */
DROP PROCEDURE IF EXISTS AssignChild;
CREATE PROCEDURE AssignChild(  
     IN chldSIN VARCHAR(8)
   , IN rmId INT
    )
BEGIN
    UPDATE CHILD
    SET RoomId = rmId
    WHERE SIN = chldSIN;
END;