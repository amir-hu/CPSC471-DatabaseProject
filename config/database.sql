DROP DATABASE IF EXISTS ChildDaycare;

CREATE DATABASE ChildDaycare;

USE ChildDaycare;

-- Set up the tables 

DROP TABLE IF EXISTS DAYCARE;
CREATE TABLE DAYCARE (
     DaycareName VARCHAR(100) NOT NULL
   , DaycareAddress VARCHAR(100) NOT NULL
   , TotalNumOfCaretakers INT
   , PRIMARY KEY (DaycareName, DaycareAddress)
);

DROP TABLE IF EXISTS ROOM;
CREATE TABLE ROOM (
    DaycareName VARCHAR(100) NOT NULL,
    DaycareAddress VARCHAR(100) NOT NULL,
    RoomId INT NOT NULL,
    SeatsAvailable INT NOT NULL,
    PRIMARY KEY (DaycareName, DaycareAddress, RoomId),
    INDEX(DaycareName, DaycareAddress), INDEX (RoomId),
    FOREIGN KEY (DaycareName, DaycareAddress) REFERENCES DAYCARE (DaycareName, DaycareAddress) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS PERSON (
     SIN VARCHAR(8) NOT NULL
   , FirstName VARCHAR(30) NOT NULL
   , LastName VARCHAR(30) NOT NULL
   , Gender VARCHAR(30) NOT NULL
   , AddrUnitNum INT NOT NULL
   , AddrStreet VARCHAR(50) NOT NULL
   , AddrCity VARCHAR(20) NOT NULL
   , AddrPostalCode VARCHAR(20) NOT NULL
   , StartDay INT
   , StartMonth INT
   , StartYear INT
   , PRIMARY KEY (SIN)
);

DROP TABLE IF EXISTS PERSON_PHONE;
CREATE TABLE PERSON_PHONE (
    SIN VARCHAR(8) NOT NULL,
    PhoneNum VARCHAR(20) NOT NULL,
    PRIMARY KEY (SIN, PhoneNum),
    INDEX (SIN),
    FOREIGN KEY (SIN) REFERENCES PERSON (SIN) ON UPDATE CASCADE ON DELETE CASCADE
);

DROP TABLE IF EXISTS EMPLOYEE;
CREATE TABLE EMPLOYEE (
    DaycareName VARCHAR(100) NOT NULL,
    DaycareAddress VARCHAR(100) NOT NULL,
    SIN VARCHAR(8) NOT NULL,
    EmployeeId INT NOT NULL,
    WorkHours DECIMAL(4,2) NOT NULL,
    HourlyRate DECIMAL(5,2) NOT NULL,
    PRIMARY KEY (DaycareName, DaycareAddress, SIN, EmployeeId),
    INDEX(DaycareName, DaycareAddress),
    INDEX(SIN),
    INDEX(EmployeeId),
    FOREIGN KEY (DaycareName, DaycareAddress) REFERENCES DAYCARE (DaycareName, DaycareAddress) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (SIN) REFERENCES PERSON (SIN) ON UPDATE CASCADE ON DELETE CASCADE
);

DROP TABLE IF EXISTS ADMIN;
CREATE TABLE ADMIN (
    SIN VARCHAR(8) NOT NULL,
    EmployeeId INT NOT NULL,
    PRIMARY KEY (SIN, EmployeeId),
    INDEX(SIN),
    INDEX(EmployeeId),
    FOREIGN KEY (SIN) REFERENCES PERSON (SIN) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (EmployeeId) REFERENCES EMPLOYEE (EmployeeId) ON UPDATE CASCADE ON DELETE CASCADE
);

DROP TABLE IF EXISTS CARETAKER;
CREATE TABLE CARETAKER (
    SIN VARCHAR(8) NOT NULL,
    EmployeeId INT NOT NULL,
    PastIncidents VARCHAR(1000),
    Availability BOOLEAN,
    PRIMARY KEY (SIN, EmployeeId),
    INDEX(SIN), INDEX(EmployeeId),
    FOREIGN KEY (SIN) REFERENCES PERSON (SIN) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (EmployeeId) REFERENCES EMPLOYEE (EmployeeId) ON UPDATE CASCADE ON DELETE CASCADE
);

DROP TABLE IF EXISTS CARETAKER_SPECIALIZATION;
CREATE TABLE CARETAKER_SPECIALIZATION (
    CaretakerSIN VARCHAR(8) NOT NULL,
    SpecializationType VARCHAR(100) NOT NULL,
    PRIMARY KEY (CaretakerSIN, SpecializationType),
    INDEX(CaretakerSIN),
    FOREIGN KEY (CaretakerSIN) REFERENCES CARETAKER (SIN) ON UPDATE CASCADE ON DELETE CASCADE
);

DROP TABLE IF EXISTS BILL;
CREATE TABLE BILL (
    BillId INT NOT NULL,
    CreatedById INT NOT NULL,
    PaymentMethod VARCHAR(30),
    AmountPending DECIMAL(6,2) NOT NULL,
    PRIMARY KEY (BillId, CreatedById),
    INDEX (CreatedById),
    FOREIGN KEY (CreatedById) REFERENCES ADMIN (EmployeeId) ON UPDATE CASCADE ON DELETE RESTRICT
);

DROP TABLE IF EXISTS PARENT_GUARDIAN;
CREATE TABLE PARENT_GUARDIAN (
    SIN VARCHAR(8) NOT NULL,
    CaretakerEmployeeId INT,
    BillId INT,
    PRIMARY KEY (SIN, CaretakerEmployeeId, BillId),
    INDEX (SIN), INDEX (CaretakerEmployeeId), INDEX (BillId),
    FOREIGN KEY (SIN) REFERENCES PERSON (SIN) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (CaretakerEmployeeId) REFERENCES CARETAKER (EmployeeId) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (BillId) REFERENCES BILL (BillId) ON UPDATE CASCADE ON DELETE RESTRICT
);

DROP TABLE IF  EXISTS CHILD;
CREATE TABLE CHILD (
    SIN VARCHAR(8) NOT NULL,
    CaretakerEmployeeId INT NOT NULL,
    ParentSIN VARCHAR(8) NOT NULL,
    DateOfBirth DATE NOT NULL,
    DaycareName VARCHAR(100) NOT NULL,
    DaycareAddress VARCHAR(100) NOT NULL,
    RoomId INT NOT NULL,
    PRIMARY KEY (SIN, CaretakerEmployeeId, ParentSIN, DaycareName, DaycareAddress, RoomId),
    INDEX (SIN), INDEX (CaretakerEmployeeId), INDEX (ParentSIN), INDEX (DaycareName), INDEX (DaycareAddress), INDEX (RoomId),
    FOREIGN KEY (SIN) REFERENCES PERSON (SIN) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (CaretakerEmployeeId) REFERENCES CARETAKER (EmployeeId) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (ParentSIN) REFERENCES PARENT_GUARDIAN (SIN) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (DaycareName, DaycareAddress, RoomId) REFERENCES ROOM (DaycareName, DaycareAddress, RoomId) ON UPDATE CASCADE ON DELETE RESTRICT
);

DROP TABLE IF EXISTS CONDITIONS;
CREATE TABLE CONDITIONS (
    ChildSIN VARCHAR(8) NOT NULL,
    ConditionName VARCHAR(100) NOT NULL,
    ConditionTreatment VARCHAR(100) NOT NULL,
    PRIMARY KEY (ChildSIN, ConditionName, ConditionTreatment),
    INDEX (ChildSIN),
    CONSTRAINT FOREIGN KEY (ChildSIN) REFERENCES CHILD (SIN) ON UPDATE CASCADE ON DELETE CASCADE
);

DROP TABLE IF EXISTS WAITLIST;
CREATE TABLE WAITLIST (
    ChildFirstName VARCHAR(30) NOT NULL,
    ChildLastName VARCHAR(30) NOT NULL,
    SubmittedById INT NOT NULL,
    PRIMARY KEY (ChildFirstName, ChildLastName, SubmittedById),
    INDEX (SubmittedById),
    FOREIGN KEY (SubmittedById) REFERENCES ADMIN (EmployeeId) ON UPDATE CASCADE ON DELETE RESTRICT
);

DROP TABLE IF EXISTS DAILY_REPORT;
CREATE TABLE DAILY_REPORT (
    ChildSIN VARCHAR(8) NOT NULL,
    ReportId INT NOT NULL,  
    CaretakerEmployeeId INT NOT NULL,   
    ReportDate DATE NOT NULL,
    ScheduleStartTime TIME NOT NULL,
    ScheduleEndTime TIME NOT NULL,
    ReportComment VARCHAR(1000),
    INDEX (ChildSIN), INDEX (CaretakerEmployeeId), INDEX (ReportId),
    PRIMARY KEY (ChildSIN, ReportDate, ReportId, CaretakerEmployeeId),
    FOREIGN KEY (ChildSIN) REFERENCES CHILD (SIN) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (CaretakerEmployeeId) REFERENCES CARETAKER (EmployeeId) ON UPDATE CASCADE ON DELETE RESTRICT
);

DROP TABLE IF EXISTS INCIDENTS;
CREATE TABLE INCIDENTS (
    ReportId INT NOT NULL,
    ActionRequired VARCHAR(100),
    INDEX (ReportId),
    PRIMARY KEY (ReportId),
    FOREIGN KEY (ReportId) REFERENCES DAILY_REPORT (ReportId) ON UPDATE CASCADE ON DELETE CASCADE
);

DROP TABLE IF EXISTS ACTIVITIES;
CREATE TABLE ACTIVITIES (
    ReportId INT NOT NULL,
    LessonsLearned VARCHAR(100),
    INDEX (ReportId),
    PRIMARY KEY (ReportId),
    FOREIGN KEY (ReportId) REFERENCES DAILY_REPORT (ReportId) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Insert data for testing

INSERT INTO PERSON (SIN, FirstName, LastName, Gender, AddrUnitNum, AddrStreet, AddrCity, AddrPostalCode, StartDay, StartMonth, StartYear)
VALUES ('12345678', 'Erin', 'Employee', 'Female', 123, 'Calgary Drive', 'Calgary', 'T2X 2E3', 09,07,2003),
       ('11122233', 'Joe', 'Fresh', 'Male',456, 'Also Clagary Dr', 'Calgary','T2X 2E3',12,10,2008), 
       ('11111111', 'First', 'Child', 'Female', 10, 'Home address','Calgary', 'T2X 2E3',19,07,2011), 
       ('11111112', 'Child', 'Parent', 'Male', 30, 'Home address','Calgary', 'T2X 2E3',09,07,2003), 
       ('99988877', 'Admin', 'Lady', 'Female', 107, 'The Daycare St', 'Calgary', 'T5R 0R4',29,'10',2008), 
       ('55566777', 'A Child', 'Person', 'Male', 10, 'Also Clagary Dr', 'Calgary', 'T5R 0R4', 12,'10',2008), 
       ('44433222', 'Dr', 'Employee', 'Male', 1000, 'The Mansion','Calgary', 'T5R 0R4', 10,10,2010);
       
INSERT INTO DAYCARE (DaycareName, DaycareAddress, TotalNumOfCaretakers)
VALUES ('Daycare One', 'Daycare Street NW', 5), 
       ('Daycare Two', 'Other Daycare Street', 30);

INSERT INTO ROOM (DaycareName, DaycareAddress, RoomId, SeatsAvailable)
VALUES ('Daycare One', 'Daycare Street NW', 1, 4),
       ('Daycare Two', 'Other Daycare Street', 1, 8),
       ('Daycare One', 'Daycare Street NW', 2, 1);

INSERT INTO PERSON_PHONE (SIN, PhoneNum)
VALUES ('12345678', '4035551234'),
       ('12345678', '4035551223'),
       ('11122233', '4035556543'),
       ('11111112', '1123456789'),
       ('99988877', '1233334444'),
       ('44433222', '4039999911');

INSERT INTO EMPLOYEE (DaycareName, DaycareAddress, SIN, EmployeeId, WorkHours, HourlyRate)
VALUES ('Daycare One', 'Daycare Street NW', '12345678', 5555, '8.0', 25.00),
       ('Daycare One', 'Daycare Street NW', '44433222', 5550, '12.0', 30.00),
       ('Daycare One', 'Daycare Street NW', '99988877', 1234, '10.0', 100.00);

INSERT INTO CARETAKER (SIN, EmployeeId, PastIncidents, Availability)
VALUES ('12345678', 5555, 'Slept during shift once.', TRUE),
       ('44433222', 5550, NULL, TRUE);

INSERT INTO ADMIN (SIN, EmployeeId)
VALUES ('99988877', 1234);

INSERT INTO BILL (BillId, CreatedById, PaymentMethod, AmountPending)
VALUES (12345, 1234, 'MasterCard', 0.00), 
       (43434, 1234, NULL, 30.00);

INSERT INTO WAITLIST (ChildFirstName, ChildLastName, SubmittedById) 
VALUES ('Possible New Child', 'Theior Family', 1234),
       ('Evil Child', 'Nice Family', 1234);

INSERT INTO CARETAKER_SPECIALIZATION (CaretakerSIN, SpecializationType)
VALUES ('12345678', 'Eating Disorders'), 
       ('12345678', 'Healthy Eating'), 
       ('44433222', 'Trauma'), 
       ('44433222', 'OCD');

INSERT INTO PARENT_GUARDIAN (SIN, CaretakerEmployeeId, BillId)
VALUES ('11122233', 5555, 12345), 
       ('11111112', 5550, 43434);

INSERT INTO CHILD (SIN, CaretakerEmployeeId, ParentSIN, DateOfBirth, DaycareName, DaycareAddress, RoomId) 
VALUES ('11111111', 5555, '11122233', STR_TO_DATE('12,10,2005', '%d, %m, %Y'), 'Daycare One', 'Daycare Street NW', 1),
       ('55566777', 5550, '11111112', STR_TO_DATE('06,06,2007', '%d, %m, %Y'), 'Daycare One', 'Daycare Street NW', 2);

INSERT INTO CONDITIONS (ChildSIN, ConditionName, ConditionTreatment)
VALUES ('11111111', 'Celiac Disease', 'Gluten Free Diet'), 
       ('11111111', 'Asthma', 'Inhaler'), 
       ('55566777', 'Asthma', 'Inhaler'), 
       ('55566777', 'OCD', 'Monitor Actions');

INSERT INTO DAILY_REPORT (ChildSIN, ReportId, CaretakerEmployeeId, ReportDate, ScheduleStartTime, ScheduleEndTime, ReportComment)
VALUES ('11111111', 1, 5555, '2020-01-01', '10:00:00', '13:00:00', 'Child took medication at 2pm. All day child did a good job!'),
       ('55566777', 2, 5550, '2020-01-01', '09:00:00', '16:41:00', 'Child took 20 min breaks as directed. Child was a bad child.'),
       ('11111111', 3, 5555, '2020-07-01', '09:00:00', '12:00:00', 'Child played well'),
       ('55566777', 4, 5550, '2020-07-01', '12:00:00', '16:41:00', 'Child was a bad child.'),
       ('55566777', 5, 5550, '2020-07-01', '15:00:00', '20:00:00', 'Child calmed down after.'),
       ('11111111', 6, 5555, '2020-07-01', '12:12:00', '12:45:00', 'Child took a lunch break'),
       ('11111111', 7, 5555, '2020-07-01', '12:45:00', '20:00:00', 'Childs parent was late to pick him up.');

INSERT INTO INCIDENTS (ReportId, ActionRequired)
VALUES (1, NULL),
       (2, 'Needs to be talked to about behaviour'),
       (3, NULL),
       (4, 'This is a bad kiddo'),
       (5, 'Talked to child about his behaviour'),
       (6, NULL),
       (7, 'Child was upset that his parent was late');

INSERT INTO ACTIVITIES (ReportId, LessonsLearned)
VALUES (1, 'Learned about space and aliens'),
       (2, 'Learned about dinosaurs'),
       (3, 'Studied computers'),
       (4, 'Could not focus on his education'),
       (5, NULL),
       (6, 'Child can cook!'),
       (7, NULL);