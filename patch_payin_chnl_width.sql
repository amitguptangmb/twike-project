-- ============================================================================
-- Payin_Chnl width fix - COMPLETE, idempotent, safe to run more than once.
--
-- Root cause: USP_ADD_USER / USP_UPDATE_USER's @Payin_Chnl parameter was
-- VARCHAR(5) in the original SQL Server source (SQL Server silently
-- truncated overflow instead of erroring). MySQL strict mode throws
-- "1406 Data too long for column 'v_Payin_Chnl'" instead - which is what
-- you're hitting. This script widens BOTH the table column and both
-- procedure parameters to VARCHAR(20) (room to spare beyond the current
-- longest channel value, "Coinnected" at 10 chars, so a future channel
-- name doesn't reopen this same bug).
--
-- Run this WHOLE FILE once against your TwikeDB database (phpMyAdmin
-- SQL tab, MySQL Workbench, or `mysql -u root -p TwikeDB < patch_payin_chnl_width.sql`).
-- The final SELECT at the bottom confirms the fix took.
-- ============================================================================

ALTER TABLE `UserMaster` MODIFY `Payin_Chnl` VARCHAR(20) NULL;

DELIMITER $$

DROP PROCEDURE IF EXISTS `USP_ADD_USER`;
CREATE PROCEDURE `USP_ADD_USER`(
    IN v_Name VARCHAR(200),
    IN v_BusinessName VARCHAR(200),
    IN v_Mobile VARCHAR(15),
    IN v_EmailId VARCHAR(100),
    IN v_Gender CHAR(1),
    IN v_Dob VARCHAR(50),
    IN v_PAN VARCHAR(10),
    IN v_Pincode VARCHAR(6),
    IN v_Address VARCHAR(200),
    IN v_City VARCHAR(100),
    IN v_State VARCHAR(100),
    IN v_GSTNo VARCHAR(16),
    IN v_Status SMALLINT,
    IN v_Password VARCHAR(20),
    IN v_SID VARCHAR(20),
    IN v_payout_flag INT,
    IN v_RTSettlementOn INT,
    IN v_Payin_Chnl VARCHAR(20),
    IN v_Payin_tax DECIMAL(18,2),
    IN v_Payout_tax DECIMAL(18,2),
    IN v_Business_Type VARCHAR(10),
    IN v_Business_Category VARCHAR(2000),
    IN v_Business_Sub_Category VARCHAR(2000),
    IN v_WebAppURL VARCHAR(2000),
    IN v_Account VARCHAR(50),
    IN v_ifsc VARCHAR(15),
    IN v_BankName VARCHAR(500),
    IN v_AccountHolderName VARCHAR(500),
    IN v_Payout_Chnl INT,
    IN v_AgentID INT,
    IN v_workingKey VARCHAR(500)
)
BEGIN
BEGIN                
 # added to prevent extra result sets from                
 # interfering with SELECT statements.                
                 
  DECLARE v_lastinsertedID int;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
                
                    
     select 'fail' as message,0 status;                
    
  END;
              
    # Insert statements for procedure here                
                 
 
                
 INSERT INTO `UserMaster`                
           (`Name`                
           ,`BusinessName`                
           ,`Mobile`                
           ,`EmailId`                
           ,`Gender`                
           ,`Dob`                
           ,`PAN`                
           ,`Pincode`                
           ,`Address`                
           ,`City`                
           ,`State`                
           ,`GSTNo`                
           ,`Status`                
           ,`CreatedOn`                
           ,`Password`                
           ,`Available_Amount`              
     ,`SID`              
     #`Callback_URL`              
     #,`Payout_Url`              
      ,`payout_flag`              
     ,`RTSettlementOn`              
      ,`Payin_Chnl`              
 #,`ClientID`            
    #  ,`ClientSecret` ,          
  , Business_Type,          
 Business_Category,          
 Business_Sub_Category,          
 WebAppURL,        
   Account,          
ifsc,          
 BankName,          
 AccountHolderName,    
 PayoutServiceID  ,  
 AgentID,CCAvenueWorkingKey  
             
     )                
     VALUES                
           (v_Name                
           ,v_BusinessName                
           ,v_Mobile                
           ,v_EmailId                
           ,v_Gender                
           ,v_Dob                
           ,v_PAN                
           ,v_Pincode                
           ,v_Address                
           ,v_City                
           ,v_State                
           ,v_GSTNo                
           ,v_Status                
           ,NOW()                
           ,v_Password                
           ,0              
      ,v_SID               
    # ,v_CallbackPayin               
   #   ,v_CallbackPayOut               
 ,v_payout_flag              
    ,v_RTSettlementOn ,              
     v_Payin_Chnl ,            
  #  v_clientID ,            
   # v_clientSecrate  ,          
 v_Business_Type,          
 v_Business_Category,          
 v_Business_Sub_Category,          
 v_WebAppURL  ,        
  v_Account,          
v_ifsc,          
 v_BankName,          
 v_AccountHolderName  ,    
  v_Payout_Chnl ,  
  v_AgentID  ,
  v_workingKey
     )                
              
 ;set v_lastinsertedID=(SELECT LAST_INSERT_ID());              
              
INSERT INTO `User_Tax_Mst`              
           (`Tax`              
           ,`UserID`              
           ,`Pay_Tax`)              
     VALUES              
           (v_Payin_tax              
           ,v_lastinsertedID,              
           v_Payout_tax);              
                
    select 'success' as message,1 status;                
                    
END;
END
$$


DROP PROCEDURE IF EXISTS `USP_UPDATE_USER`;
CREATE PROCEDURE `USP_UPDATE_USER`(
    IN v_UserID VARCHAR(200),
    IN v_Name VARCHAR(200),
    IN v_BusinessName VARCHAR(200),
    IN v_Mobile VARCHAR(15),
    IN v_EmailId VARCHAR(100),
    IN v_Gender CHAR(1),
    IN v_Dob VARCHAR(50),
    IN v_PAN VARCHAR(10),
    IN v_Pincode VARCHAR(6),
    IN v_Address VARCHAR(200),
    IN v_City VARCHAR(100),
    IN v_State VARCHAR(100),
    IN v_GSTNo VARCHAR(16),
    IN v_Status SMALLINT,
    IN v_Password VARCHAR(20),
    IN v_SID VARCHAR(20),
    IN v_CallbackPayin VARCHAR(500),
    IN v_CallbackPayOut VARCHAR(500),
    IN v_payout_flag INT,
    IN v_RTSettlementOn INT,
    IN v_Payin_Chnl VARCHAR(20),
    IN v_Payin_tax DECIMAL(18,2),
    IN v_Payout_tax DECIMAL(18,2),
    IN v_FailedCount INT,
    IN v_Business_Type VARCHAR(10),
    IN v_Business_Category VARCHAR(2000),
    IN v_Business_Sub_Category VARCHAR(2000),
    IN v_WebAppURL VARCHAR(2000),
    IN v_Account VARCHAR(50),
    IN v_ifsc VARCHAR(15),
    IN v_BankName VARCHAR(500),
    IN v_AccountHolderName VARCHAR(500),
    IN v_Payout_Chnl INT,
    IN v_AgentID INT,
    IN v_workingKey VARCHAR(500)
)
BEGIN
BEGIN                  
 # added to prevent extra result sets from                  
 # interfering with SELECT statements.                  
                   
  DECLARE v_lastinsertedID int;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
                  
                      
     select 'fail' as message,0 status;                  
    
  END;
                
    # Insert statements for procedure here                  
                  
IF ((v_Password !='')) THEN
  UPDATE `UserMaster`              
   SET `Name` = v_Name              
      ,`BusinessName` = v_BusinessName              
      ,`Mobile` = v_Mobile              
      ,`EmailId` = v_EmailId              
      ,`Gender` = v_Gender              
      ,`Dob` = v_Dob              
      ,`PAN` = v_PAN              
      ,`Pincode` = v_Pincode              
      ,`Address` = v_Address              
      ,`City` = v_City              
      ,`State` = v_State              
      ,`GSTNo` = v_GSTNo              
                  
      ,`Password` = v_Password              
      ,`SID` = v_SID              
   ,`Status` = v_Status              
      ,`payout_flag` = v_payout_flag              
      ,`Callback_URL` = v_CallbackPayin              
      ,`Payin_Chnl` = v_Payin_Chnl              
      ,`Payout_Url` = v_CallbackPayOut              
                
      ,`RTSettlementOn` = v_RTSettlementOn              
      ,`FailedCount` = v_FailedCount  ,          
             
     Business_Type =v_Business_Type,          
 Business_Category =v_Business_Category,          
 Business_Sub_Category =v_Business_Sub_Category,          
 WebAppURL =v_WebAppURL  ,        
         
   Account =v_Account,          
ifsc=v_ifsc,          
 BankName=v_BankName,          
 AccountHolderName  =v_AccountHolderName,        
    PayoutServiceID  =v_Payout_Chnl ,    
  AgentID  =v_AgentID    
 
 WHERE UserId =v_UserID;                
              
 UPDATE `User_Tax_Mst`              
   SET `Tax` = v_Payin_tax               
      ,`Pay_Tax` = v_Payout_tax              
 WHERE `UserID` =v_UserID;                
                  
    select 'success' as message,1 status;
  ELSE
  UPDATE `UserMaster`              
   SET `Name` = v_Name              
      ,`BusinessName` = v_BusinessName              
      ,`Mobile` = v_Mobile              
      ,`EmailId` = v_EmailId              
      ,`Gender` = v_Gender                ,`Dob` = v_Dob              
      ,`PAN` = v_PAN              
      ,`Pincode` = v_Pincode              
      ,`Address` = v_Address              
      ,`City` = v_City              
      ,`State` = v_State              
      ,`GSTNo` = v_GSTNo              
      ,`SID` = v_SID              
   ,`Status` = v_Status              
   ,`payout_flag` = v_payout_flag              
      ,`Callback_URL` = v_CallbackPayin              
      ,`Payin_Chnl` = v_Payin_Chnl              
      ,`Payout_Url` = v_CallbackPayOut              
                
      ,`RTSettlementOn` = v_RTSettlementOn              
      ,`FailedCount` = v_FailedCount ,          
       Business_Type =v_Business_Type,          
 Business_Category =v_Business_Category,          
 Business_Sub_Category =v_Business_Sub_Category,          
 WebAppURL =v_WebAppURL,        
    Account =v_Account,          
ifsc=v_ifsc,          
 BankName=v_BankName,          
 AccountHolderName  =v_AccountHolderName,        
    PayoutServiceID  =v_Payout_Chnl ,    
  AgentID  =v_AgentID   

     
     
 WHERE UserId =v_UserID;
  END IF;                
              
 UPDATE `User_Tax_Mst`              
  SET `Tax` = v_Payin_tax               
      ,`Pay_Tax` = v_Payout_tax              
 WHERE `UserID` =v_UserID;                
                  
    select 'success' as message,1 status;                  
                
             
END;
END
$$

DELIMITER ;

-- Verification - run this and check the results:
-- CHARACTER_MAXIMUM_LENGTH should read 20 on all three rows below.
-- If any row is missing or still shows 5/10, the corresponding
-- ALTER/CREATE above did not apply (check for an error above it).
SELECT 'UserMaster.Payin_Chnl (table column)' AS what, CHARACTER_MAXIMUM_LENGTH
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'UserMaster' AND COLUMN_NAME = 'Payin_Chnl'
UNION ALL
SELECT CONCAT(SPECIFIC_NAME, '.v_Payin_Chnl (procedure param)'), CHARACTER_MAXIMUM_LENGTH
FROM INFORMATION_SCHEMA.PARAMETERS
WHERE SPECIFIC_SCHEMA = DATABASE() AND PARAMETER_NAME = 'v_Payin_Chnl'
  AND SPECIFIC_NAME IN ('USP_ADD_USER', 'USP_UPDATE_USER');
