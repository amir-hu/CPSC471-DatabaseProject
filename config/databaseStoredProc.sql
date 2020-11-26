USE ChildDaycare;

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


/* Class: CHILD
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
      , cndtn.Condition
      , cndtn.Treatment
    FROM CHILD as chld
    INNER JOIN
        CONDITIONS as cndtn
        ON cndtn.ChildSIN = chld.SIN
    INNER JOIN
        PERSON as prsn
        ON prsn.SIN = chld.SIN
    WHERE chld.SIN = childSIN;
END;



/* Class: CHILD
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
       , dlyRprt.StartTime
       , dlyRprt.EndTime
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


/* Class: CHILD
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
         ON chld.RoomId = rm.RoomId
    WHERE chld.SIN = childSIN;
END;


/* Class: PARENT_GUARDIAN
 * Method: SelectCaretaker()
 * Description: View all caretakers and their info
 */
DROP PROCEDURE IF EXISTS SelectCaretaker;
CREATE PROCEDURE SelectCaretaker ()

BEGIN
    SELECT
         prsn.FirstName
       , prsn.LastName
       , prsn.Gender
       , prsn.StartDay
       , prsn.StartMonth
       , prsn.StartYear
       , crtkr.Availability
       , crtkrSpclztn.SpecializationType
    FROM CARETAKER as crtkr
    INNER JOIN
         PERSON as prsn
         ON prsn.SIN = crtkt.SIN
    INNER JOIN
         CARETAKER_SPECIALIZATION as crtkrSpclztn
         ON crtkrSpclztn.CaretakerSIN = crtkr.SIN;
END;


/* Class: PARENT_GUARDIAN
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


/* Class: PARENT_GUARDIAN
 * Method: PayBill()
 * Description: Parent pays outstanding bill
 * @param BillId - id of the bill to be paid
 * @param amountPending - the amount left over after payment
 * @param PaymentMethod - payment method of parent_guardian
 */
DROP PROCEDURE IF EXISTS PayBill;
CREATE PROCEDURE PayBill(
      IN bllId INT
    , IN pmntMthd VARCHAR(30)
    , IN amntPndg DECIMAL(6,2)
    )
    
BEGIN
    UPDATE BILL
    SET AmountPending = amntPndg,
        PaymentMethod = pmntMthd
    WHERE BillId = bllId;
END;


/* Class: ADMIN
 * Method: AddToWaitlist()
 * Description: Add a new Child/family to the waitlist
 * @param ChildName - Name of child to be inserted
 * @param FamilyName - Last name of child/family
 * @param EmployeeId - ID of employee submitting
 */
DROP PROCEDURE IF EXISTS AddToWaitlist;
CREATE PROCEDURE AddToWaitlist(
      IN chldnme VARCHAR(30)
    , IN Fmlynme VARCHAR(30)
    , IN emplyId INT
    )
    
BEGIN
    INSERT INTO WAITLIST (ChildName, FamilyName, SubmittedById)
    VALUES (chldnme , Fmlynme, emplyId);
END;


/* Class: ADMIN
 * Method: CreateBill()
 * Description: Add a bill
 * @param Amount - amount of bill
 * @param EmployeeId - ID of employee submitting
 */
DROP PROCEDURE IF EXISTS CreateBill;
CREATE PROCEDURE CreateBill(
      IN amnt DECIMAL(6,2)
    , IN emplynme VARCHAR(30)
    )

BEGIN 
    INSERT INTO BILL (CreatedById, AmountPending)
    VALUES (emplynme, amnt);
END;
 

/* Class: ADMIN
 * Method: AddEmployee()
 * Description: Add a new Employee
 * @param FirstName - First name
 * @param LastName - Last name
 * @param Gender
 * @param AddrUnitNum - Unit number
 * @param AddrStreet - Street address
 * @param AddrCity - City
 * @param AddrPostalCode - Postal code
 * @param StartDay - Start Day
 * @param StartMonth - StartMonth
 * @param StartYear
 * @param Daycare - Daycare Name
 * @param Address - Daycare Address
 * @param SIN - SIN of new EmployeeID
 * @param WorkHours - Hours available to work
 * @param PhoneNum
 */
DROP PROCEDURE IF EXISTS AddEmployee;
CREATE PROCEDURE AddEmployee(
      IN FrstNm VARCHAR(30) 
    , IN LstNm VARCHAR(30)
    , IN Gndr VARCHAR(30)
    , IN AddrUntNm INT
    , IN AddrStrt VARCHAR(50)
    , IN AddrCty VARCHAR(20)
    , IN AddrPstlCde VARCHAR(30)
    , IN StrtDy VARCHAR(10)
    , IN StrtMnth VARCHAR(9)
    , IN StrtYr INT
    , IN Dycre VARCHAR(100)
    , IN Addrss VARCHAR(9) 
    , IN empSIN VARCHAR(8)
    , IN WrkHrs DECIMAL(4,2)
    , IN PhneNm VARCHAR(20)
        )

BEGIN 
    INSERT INTO PERSON (SIN, FIRSTNAME, LASTNAME, GENDER, ADDRUNITNUM, ADDRSTREET, ADDRCITY, ADDRPOSTALCODE, STARTDAY, STARTMONTH, STARTYEAR)
    VALUES (empSIN, FrstNm, LstNm, Gndr, AddrUntNm, AddrStrt, AddrCty, AddrPstlCde, StrtDy, StrtMnth, StrtYr);

    INSERT INTO EMPLOYEE (DaycareName, DaycareAddress, SIN, WorkHours)
    VALUES (Dycre, Addrss, SIN, WrkHrs);

    INSERT INTO PERSON_PHONE (SIN, PHONENUM)
    VALUES (empSIN, PhneNm);
END;


/* Class: ADMIN
 * Method: RemoveEmployee()
 * Description: Add a bill
 * @param EmployeeId - ID of employee to remove
 */
DROP PROCEDURE IF EXISTS RemoveEmployee;
CREATE PROCEDURE RemoveEmployee(
      IN emplyid INT
    )

BEGIN 
    DELETE FROM EMPLOYEE
    WHERE EmployeeId = emplyid; 
END;


/* Class: EMPLOYEE
 * Method: GetDaycare()
 * Description: Gets the daycare the Employee works at
 * @param EmployeeID - Id of employee
 */

DROP PROCEDURE IF EXISTS GetDaycare;
CREATE PROCEDURE GetDaycare(
      IN emplyid INT
    )

BEGIN
    SELECT 
         DaycareName
       , DaycareAddress
       , TotalNumOfCaretakers
    FROM EMPLOYEE
    WHERE EmployeeId = emplyId;
END;


/* Class: DAYCARE
 * Method: GetEmployees()
 * Description: Gets a list of all employees
 * @param daycareName - the daycarename
 * @param daycareAddress - the address of the daycre
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
        and emp.DaycareAddress = dycreAddrss;
END;


/* Class: DAYCARE
 * Method: DaycareGetRooms()
 * Description: Get a list of rooms at the daycare
 * @param DaycareName - Name of the daycare
 * @param address - the address of the daycare
 */
DROP PROCEDURE IF EXISTS DaycareGetRooms;
CREATE PROCEDURE DaycareGetRooms(
      IN dycreNme VARCHAR(100)
    , IN dycreAddrss VARCHAR(100)
    )

BEGIN
    SELECT
         RoomId
       , SeatsAvailable
    FROM ROOM
    WHERE DaycareName = dycreNme
        and DaycareAddress = dycreAddrss;
END;


/* Class: CARETAKER
 * Method: AddReport()
 * Description: Insert report into Daily_report table
 * @param EmployeeId - EmployeeID of the caretaker inserting the report
 * @param date - Date of the report
 * @param StartTime - Start time of the report
 * @param Endtime - End time of the report
 * @param ChildSIN - SIN of child the report is about
 * @param ReportComment - Text of report
 */
DROP PROCEDURE IF EXISTS AddReport;
CREATE PROCEDURE AddReport(
      IN  EmplyId INT  
    , IN  rptDte DATE
    , IN  StrtTme TIME    
    , IN  Endtme TIME
    , IN  ChldSIN VARCHAR(8)
    , IN  ReprtCmmnt VARCHAR(1000)
    )

BEGIN 
    INSERT INTO DAILY_REPORT (ChildSIN, ReportDate, CaretakerEmployeeId, StartTimne, EndTime, ReportComment)
    VALUES (ChldSIN, rptDte, EmplyId, StrtTme, EndTme, RprtCmmnt);
END;


/* Class: CARETAKER
 * Method: CaretakerGetDailyReport()
 * Description: Get daily report submitted by that caretaker
 * @param CaretakerId - Id of caretaker
 * @param Date - Date wanted
 * @param ChildSIN - SIN of child repoported on
 */
DROP PROCEDURE IF EXISTS CaretakerGetDailyReport;
CREATE PROCEDURE CaretakerGetDailyReport(
      IN  CrtkrId INT  
    , IN  ChldSIN VARCHAR(8)
    , IN  dte DATE
    )
BEGIN 
    SELECT
         ChildSIN
       , StartTime
       , EndTime
       , ReportComment
    FROM DAILY_REPORT
    WHERE ChildSIN = ChldSIN
        AND CaretakerEmployeeId = CrtkrId
        AND ReportDate = Dte;
END;


/* Class: ROOM
 * Method: AssignChild()
 * Description: Assign a child to the room
 * @param ChildSin- SIN of CHILD
 * @param RoomId - Id of Room being assigned
 */
DROP PROCEDURE IF EXISTS AssignChild;
CREATE PROCEDURE AssignChild(  
     IN  chldSIN VARCHAR(8)
    , IN  rmId INT
    )
BEGIN
    UPDATE CHILD
    SET RoomId = rmId
    WHERE SIN = chldSIN;
END;

