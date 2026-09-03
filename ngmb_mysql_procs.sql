DELIMITER $$

DROP PROCEDURE IF EXISTS `add_Benificiary`;
CREATE PROCEDURE `add_Benificiary`(
    IN v_AgentId VARCHAR(20),
    IN v_BeneName VARCHAR(20),
    IN v_Mode VARCHAR(20),
    IN v_BankName VARCHAR(20),
    IN v_Ifsc VARCHAR(20),
    IN v_BranchName VARCHAR(20),
    IN v_AccountNo VARCHAR(20),
    IN v_SenderId VARCHAR(15),
    IN v_Status VARCHAR(15),
    IN v_Date_Of_Registration VARCHAR(20)
)
BEGIN
#BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	DECLARE v_CardNo varchar(20);
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN

	select '0' as message,1 transactionID;
	
  END;

	
	

	IF (((select count(*) from AddBeneficiary where AccountNo=v_AccountNo and SenderId=v_SenderId )>=1 )) THEN
  #select distinct 'Already Exist' as message from  AddBeneficiary
			update AddBeneficiary set  AgentId=v_AgentId
           ,BeneName=v_BeneName
           ,BankName=v_BankName
           ,Ifsc=v_Ifsc
           ,BranchName=v_BranchName
           ,AccountNo=v_AccountNo
           ,Date_Of_Registration=v_Date_Of_Registration
		   ,Status='Verified'
          where SenderId=v_SenderId and AccountNo=v_AccountNo

		  ;select '1' as message,0 transactionID;
  ELSE
  #set v_Status='Not Verified';
#if v_Mode='2'
#Begin
#set v_Status='Verified';
#END
set v_CardNo=(select ( case when IFNULL(max(CardNo),'0')='0' then '10000' else (cast(max(CardNo) AS SIGNED)+1) end)  from AddBeneficiary);
 INSERT INTO AddBeneficiary
           (CardNo
		   ,TransactionNo
           ,AgentId
           ,BeneName
           ,Mode
           ,BankName
           ,Ifsc
           ,BranchName
           ,AccountNo
           ,Date_Of_Registration
           ,Status
		   ,SenderId)
     VALUES
           (v_CardNo
		   ,v_CardNo
           ,v_AgentId
           ,v_BeneName
           ,v_Mode
           ,v_BankName
           ,v_Ifsc
           ,v_BranchName
           ,v_AccountNo
           ,v_Date_Of_Registration
           ,v_Status
		   ,v_SenderId)
	;select '1' as message,0 transactionID;
  END IF;
END
$$

DROP PROCEDURE IF EXISTS `addAmountPayIN`;
CREATE PROCEDURE `addAmountPayIN`(
    IN v_flag INT,
    IN v_userID INT,
    IN v_Amount DECIMAL(10,2)
)
BEGIN
BEGIN    
 # added to prevent extra result sets from    
 # interfering with SELECT statements.    
     
    
IF ((v_flag=1)) THEN
  update `UserMaster` set `Collection_Amount` = `Collection_Amount` + v_Amount  where `UserId`=v_userID;   
INSERT INTO MONEY_TRANSFER_PAYIN  
           (SERVICE_NAME  
           ,AMOUNT  
           ,AccountNo  
           ,MobileNo  
           ,BENEFICIAL_ID  
           ,CREATED_ON  
           ,CREATED_BY  
           ,ServiceVendor  
           ,ModOfPayment  
     ,Tax   
     ,Tax_AMOUNT  
     ,Total_AMOUNT  
     ,user_order_id  
     ,BankName   
     ,IFSC  
     ,HolderName  
     ,Total_CD_Amt  
     ,TrFrom  
     ,TRSID,  
     REQUEST_IP_ADDRESS,  
     STATUS  
     )  
     VALUES('Refund Access Amount in PayIn'  
           ,v_AMOUNT  
           ,''  
           ,''  
           ,''  
           ,NOW()  
           ,v_userID  
           ,''  
           ,'Refund'  
     ,0   
     ,0  
     ,v_AMOUNT  
     ,CONCAT('Refund_',v_AMOUNT,'_',NOW() )  
     ,''   
     ,''  
     ,v_userID  
     ,'',  
      '',  
   '',  
     '',  
     'SUCCESS'  
     );
  END IF;    
IF ((v_flag=2)) THEN
  update `UserMaster` set `Collection_Amount` = `Collection_Amount` - v_Amount  where `UserId`=v_userID;    
  
INSERT INTO MONEY_TRANSFER_PAYIN  
           (SERVICE_NAME  
           ,AMOUNT  
           ,AccountNo  
           ,MobileNo  
           ,BENEFICIAL_ID  
           ,CREATED_ON  
           ,CREATED_BY  
           ,ServiceVendor  
           ,ModOfPayment  
     ,Tax   
     ,Tax_AMOUNT  
     ,Total_AMOUNT  
     ,user_order_id  
     ,BankName   
     ,IFSC  
     ,HolderName  
     ,Total_CD_Amt  
     ,TrFrom  
     ,TRSID,  
     REQUEST_IP_ADDRESS,  
     STATUS  
     )  
     VALUES('Chargeback Amount in PayIn'  
           ,v_AMOUNT  
           ,''  
           ,''  
           ,''  
           ,NOW() 
           ,v_userID  
           ,''  
           ,'Chargeback'  
     ,0   
     ,0  
     ,v_AMOUNT  
     ,CONCAT('Chargeback_',v_AMOUNT,'_',NOW() )  
     ,''   
     ,''  
     ,v_userID  
     ,'',  
      '',  
   '',  
     '',  
     'SUCCESS'  
     );
  END IF;    
  
  
  
    
    
END;
END
$$

DROP PROCEDURE IF EXISTS `addAmountPayout`;
CREATE PROCEDURE `addAmountPayout`(
    IN v_flag INT,
    IN v_userID INT,
    IN v_Amount DECIMAL(10,2)
)
BEGIN
BEGIN      
 # added to prevent extra result sets from      
 # interfering with SELECT statements.      
       
      
IF ((v_flag=1)) THEN
  update `UserMaster` set `Available_Amount` = `Available_Amount` + v_Amount  where `UserId`=v_userID;      
INSERT INTO MONEY_TRANSFER_PAYOUT    
           (SERVICE_NAME    
           ,AMOUNT    
           ,AccountNo    
           ,MobileNo    
           ,BENEFICIAL_ID    
           ,CREATED_ON    
           ,CREATED_BY    
           ,ServiceVendor    
           ,ModOfPayment    
     ,Tax     
     ,Tax_AMOUNT    
     ,Total_AMOUNT    
     ,user_order_id    
     ,BankName     
     ,IFSC    
     ,HolderName    
     ,Total_CD_Amt,    
     REQUEST_IP_ADDRESS,    
       ActivePayout,    
    STATUS    
     )    
     VALUES('Wallet Recharge'    
           ,v_AMOUNT    
           ,''    
           ,''    
           ,''    
           ,NOW()   
           ,v_userID    
           ,''    
           ,'WalletRecharge'    
     ,0     
     ,0    
     ,v_AMOUNT    
     ,CONCAT('Wallet_Recharge_',v_AMOUNT,'_',NOW())    
     ,''     
     ,''    
     ,v_userID    
     ,'',    
     '',    
     0,    
     'SUCCESS'    
     );
  END IF;      
IF ((v_flag=2)) THEN
  update `UserMaster` set `Available_Amount` = `Available_Amount` - v_Amount  where `UserId`=v_userID;      
INSERT INTO MONEY_TRANSFER_PAYOUT    
           (SERVICE_NAME    
           ,AMOUNT    
           ,AccountNo    
           ,MobileNo    
           ,BENEFICIAL_ID    
           ,CREATED_ON    
           ,CREATED_BY    
           ,ServiceVendor    
           ,ModOfPayment    
     ,Tax     
     ,Tax_AMOUNT    
     ,Total_AMOUNT    
     ,user_order_id    
     ,BankName     
     ,IFSC    
     ,HolderName    
     ,Total_CD_Amt,    
     REQUEST_IP_ADDRESS,    
       ActivePayout,    
    STATUS    
     )    
     VALUES('Chargeback Detected Amount in PayOut'    
           ,v_AMOUNT    
           ,''    
           ,''    
           ,''    
           ,NOW()  
           ,v_userID    
           ,''    
           ,'Chargeback'    
     ,0     
     ,0    
     ,v_AMOUNT    
     ,CONCAT('Chargeback_',v_AMOUNT,'_',NOW())    
     ,''     
     ,''    
     ,v_userID    
     ,'',    
     '',    
     0,    
     'SUCCESS'    
     );
  END IF;      
      
      
END;
END
$$

DROP PROCEDURE IF EXISTS `Admin_Dashbaord`;
CREATE PROCEDURE `Admin_Dashbaord`(
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10),
    IN v_UserID INT
)
BEGIN
BEGIN  
   
   
 SELECT   
 sum(CAST(m.TOTAL_AMOUNT AS DOUBLE)) as total,m.ModOfPayment  
  FROM MONEY_TRANSFER_PAYIN m inner join UserMaster u on m.CREATED_BY=u.UserId  
  where DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>= v_fromdate  
 and  (( v_userID  = 0 and u.UserId=u.UserId)  
    or (v_userID  <> 0 and  u.UserId = v_userID)  
 )  
 and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate and    
  m.STATUS in('processed','SUCCESS')   group by m.ModOfPayment;  
   
END;
END
$$

DROP PROCEDURE IF EXISTS `Admin_Dashbaord_payout`;
CREATE PROCEDURE `Admin_Dashbaord_payout`(
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10),
    IN v_UserID INT
)
BEGIN
BEGIN  
   
   
 SELECT   
 sum(CAST(m.TOTAL_AMOUNT AS DOUBLE)) as total,m.ModOfPayment  
  FROM MONEY_TRANSFER_PAYOUT m inner join UserMaster u on m.CREATED_BY=u.UserId  
  where DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>= v_fromdate  
 and  (( v_userID  = 0 and u.UserId=u.UserId)  
    or (v_userID  <> 0 and  u.UserId = v_userID)  
 )  
 and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate and    
  m.STATUS in('processed','SUCCESS')   group by m.ModOfPayment;  
   
END;
END
$$

DROP PROCEDURE IF EXISTS `Allow_Payout_Services`;
CREATE PROCEDURE `Allow_Payout_Services`(
    IN v_UserId INT
)
BEGIN
BEGIN      
 # added to prevent extra result sets from      
 # interfering with SELECT statements.      
       
      
    # Insert statements for procedure here      
 SELECT       
      0 as AllowSevice,PayoutServiceID as ServiceID      
  FROM UserMaster where UserId=v_UserId and payout_flag=1;      
      
END;
END
$$

DROP PROCEDURE IF EXISTS `Allow_Payout_Services_USER`;
CREATE PROCEDURE `Allow_Payout_Services_USER`(
    IN v_userID INT
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

  select payout_flag   from UserMaster  where Status = 1 and UserId=v_userID and payout_flag != 0;


END;
END
$$

DROP PROCEDURE IF EXISTS `busniessReportDeateWise`;
CREATE PROCEDURE `busniessReportDeateWise`(
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10)
)
BEGIN
BEGIN
	
	select mk.totalAmount as total,ut.BusinessName,mk.CREATED_ON as date from (select   SUM(mt.AMOUNT) as totalAmount,mt.CREATED_BY,DATE_FORMAT(mt.CREATED_ON, '%Y-%m-%dT%H:%i:%s') as CREATED_ON
		from MONEY_TRANSFER_PAYIN mt  where  mt.ModOfPayment in('collection')
		#and  IFNULL(UTR,'0')<>'0'
		and mt.STATUS in('paid','Success','processing','SUCCESS') 

		and  DATE_FORMAT(mt.CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate
		and DATE_FORMAT(mt.CREATED_ON, '%Y-%m-%dT%H:%i:%s')<= v_todate  group by mt.CREATED_BY,DATE_FORMAT(mt.CREATED_ON, '%Y-%m-%dT%H:%i:%s')) mk join UserMaster ut on mk.CREATED_BY=ut.UserId;


	

END;
END
$$

DROP PROCEDURE IF EXISTS `Delete_Benificiary`;
CREATE PROCEDURE `Delete_Benificiary`(
    IN v_Ifsc VARCHAR(20),
    IN v_AccountNo VARCHAR(20)
)
BEGIN
#BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	DECLARE v_CardNo varchar(20);
DECLARE v_Status	varchar(20);
	
	IF (((select count(*) from AddBeneficiary where AccountNo=v_AccountNo )>=1 )) THEN
  #select distinct 'Already Exist' as message from  AddBeneficiary
			DELETE FROM AddBeneficiary 
          where AccountNo=v_AccountNo and Ifsc=v_Ifsc

		  ;select '1' as message,0 transactionID;
  END IF;
END
$$

DROP PROCEDURE IF EXISTS `get_15min_transaction`;
CREATE PROCEDURE `get_15min_transaction`(
    
)
BEGIN
BEGIN  
 # added to prevent extra result sets from  
 # interfering with SELECT statements.  
   
  
SELECT  TRANSACTIONID,CREATED_ON FROM MONEY_TRANSFER_PAYIN where  STATUS='CREATED' and   ModOfPayment='Collection' and CREATED_ON between DATE_ADD(NOW(), INTERVAL -20 MINUTE) and DATE_ADD(NOW(), INTERVAL -10 MINUTE);  
END;
END
$$

DROP PROCEDURE IF EXISTS `Get_Agent_UserList`;
CREATE PROCEDURE `Get_Agent_UserList`(
    IN v_AgentID INT
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	
SELECT UserId
      ,Name
      ,BusinessName
      ,Mobile
      ,EmailId
      ,Gender
      ,Dob
      ,PAN
      ,Pincode
      ,Address
      ,City
      ,State
      ,GSTNo
      ,case when Status=1 then 'Active' else 'In Active' end Status
     ,Available_Amount,Collection_Amount
      FROM UserMaster where AgentID=v_AgentID;
END;
END
$$

DROP PROCEDURE IF EXISTS `get_all_transaction`;
CREATE PROCEDURE `get_all_transaction`(
    
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

  SELECT  TRANSACTIONID,CREATED_ON FROM MONEY_TRANSFER_PAYIN where STATUS='CREATED' and ModOfPayment='Collection' and CREATED_ON between DATE_ADD(0, INTERVAL DATEDIFF(NOW(), 1) DAY) and DATE_ADD(DATE_ADD(DATE_ADD(NOW(), INTERVAL 30 MINUTE), INTERVAL 5 HOUR), INTERVAL -40 MINUTE);
END;
END
$$

DROP PROCEDURE IF EXISTS `GET_CCAVENUE_WORKING_KEY`;
CREATE PROCEDURE `GET_CCAVENUE_WORKING_KEY`(
    IN v_accessCode VARCHAR(100)
)
BEGIN
BEGIN
	
select `CCAvenueWorkingKey` from UserMaster   where `CCAvenueAccessCode` = v_accessCode;
END;
END
$$

DROP PROCEDURE IF EXISTS `get_failure_transaction`;
CREATE PROCEDURE `get_failure_transaction`(
    
)
BEGIN
BEGIN  
 # added to prevent extra result sets from  
 # interfering with SELECT statements.  
   
SELECT  TRANSACTIONID FROM MONEY_TRANSFER_PAYIN where  STATUS='FAILED' and   ModOfPayment='Collection' and CREATED_ON between DATE_ADD(DATE_ADD(DATE_ADD(NOW(), INTERVAL 30 MINUTE), INTERVAL 5 HOUR), INTERVAL -40 MINUTE) and DATE_ADD(DATE_ADD(DATE_ADD(NOW(), INTERVAL 30 MINUTE), INTERVAL 5 HOUR), INTERVAL -35 MINUTE);  
END;
END
$$

DROP PROCEDURE IF EXISTS `GET_PAYIN_CHANEL`;
CREATE PROCEDURE `GET_PAYIN_CHANEL`(
    IN v_ClientID VARCHAR(50),
    IN v_ClientSecret VARCHAR(50)
)
BEGIN
BEGIN          
 # added to prevent extra result sets from          
 # interfering with SELECT statements.        
           
          
    # Insert statements for procedure here          
 SELECT IFNULL(SID,'0') SID,Payin_Chnl,'0' user_order_id,UserId as USERID,InnerPayinFlag,CCAvenueWorkingKey,CCAvenueMID,RequestHashKey FROM UserMaster WHERE ClientID=v_ClientID and  ClientSecret= v_ClientSecret and Status=1;        
END;
END
$$

DROP PROCEDURE IF EXISTS `GET_PAYIN_CHANEL_CCAVENUE`;
CREATE PROCEDURE `GET_PAYIN_CHANEL_CCAVENUE`(
    IN v_ClientID VARCHAR(50),
    IN v_ClientSecret VARCHAR(50)
)
BEGIN
BEGIN      
 # added to prevent extra result sets from      
 # interfering with SELECT statements.    
       
      
    # Insert statements for procedure here      
 SELECT IFNULL(SID,'0') SID,Payin_Chnl,'0' user_order_id,UserId as USERID,CCAvenueMID,CCAvenueWorkingKey,CCAvenueAccessCode FROM UserMaster WHERE ClientID=v_ClientID and  ClientSecret= v_ClientSecret and Status=1;    
END;
END
$$

DROP PROCEDURE IF EXISTS `get_payout_balance_USER`;
CREATE PROCEDURE `get_payout_balance_USER`(
    IN v_userID INT
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

  select Available_Amount   from UserMaster  where Status = 1 and UserId=v_userID and payout_flag != 0;


END;
END
$$

DROP PROCEDURE IF EXISTS `GET_PAYOUT_CALLBACKURL`;
CREATE PROCEDURE `GET_PAYOUT_CALLBACKURL`(
    IN v_UserId INT
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	        
	select IFNULL(payout_url,0) payout_url from UserMaster where UserId=v_UserId;
END;
END
$$

DROP PROCEDURE IF EXISTS `get_requestHash`;
CREATE PROCEDURE `get_requestHash`(
    IN v_ClientID VARCHAR(200)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

   select `ClientSecret`,`RequestHashKey`  FROM `ngmb`.`UserMaster`  where `ClientID` = v_ClientID;
END;
END
$$

DROP PROCEDURE IF EXISTS `Get_Sender_ALL_Data`;
CREATE PROCEDURE `Get_Sender_ALL_Data`(
    IN v_Sender_Mobile VARCHAR(15),
    IN v_agentId VARCHAR(50)
)
BEGIN
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN

		SELECT 'False' is_status;
	
  END;

	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
	

	select 'True' is_status, Name senderName,Sender_Mobile senderMobile,
	(select Wallet_Limit from UserMaster where userid= v_agentId) totLimit,'' consumLimit from Sender_Master
	where Sender_Mobile=v_Sender_Mobile and Created_By=v_agentId;

	#select cardNo,agentId,beneName,beneMobile,bankName,ifsc,accountNo,date_Of_Registration,status,senderId 
	
	#from AddBeneficiary where senderid=v_Sender_Mobile and agentid=v_agentId
	

END;
END
$$

DROP PROCEDURE IF EXISTS `Get_Sender_BenList_Data`;
CREATE PROCEDURE `Get_Sender_BenList_Data`(
    IN v_Sender_Mobile VARCHAR(15),
    IN v_agentId VARCHAR(50)
)
BEGIN
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN

		SELECT 'Not found' as message,1 transactionID;
	
  END;

	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
	

	select cardNo,agentId,beneName,beneMobile,bankName,branchName,ifsc,accountNo,date_Of_Registration,status,senderId 
	
	from AddBeneficiary where senderid=v_Sender_Mobile and agentid=v_agentId;
	

END;
END
$$

DROP PROCEDURE IF EXISTS `GET_SID_DATA`;
CREATE PROCEDURE `GET_SID_DATA`(
    IN v_SID VARCHAR(300)
)
BEGIN
BEGIN          
 # added to prevent extra result sets from          
 # interfering with SELECT statements.        
           

 SELECT 
      s.SID
      ,s.ClientID
      ,s.ClientSecret
      ,s.Checksum
  FROM SIDcreds s  where s.SID = v_SID and s.ActiveFlag=1;
   
END;
END
$$

DROP PROCEDURE IF EXISTS `GET_SID_DATA_PAYTARA`;
CREATE PROCEDURE `GET_SID_DATA_PAYTARA`(
    IN v_SID VARCHAR(300)
)
BEGIN
BEGIN          
 # added to prevent extra result sets from          
 # interfering with SELECT statements.        
           

 SELECT 
      s.SID
      ,s.merchantId
      ,s.channel
      ,s.Token
  FROM PAYTARAcredMST s  where s.SID = v_SID and s.ActiveFlag=1;
   
END;
END
$$

DROP PROCEDURE IF EXISTS `GET_USER_BY_ID`;
CREATE PROCEDURE `GET_USER_BY_ID`(
    IN v_UserId INT
)
BEGIN
BEGIN        
 # added to prevent extra result sets from        
 # interfering with SELECT statements.        
         
        
    # Insert statements for procedure here        
 /****** Script for SelectTopNRows command from SSMS  ******/        
SELECT um.`UserId`      
      ,um.`Name`      
      ,um.`BusinessName`      
      ,um.`Mobile`      
      ,um.`EmailId`      
      ,um.`Gender`      
      ,um.`Dob`      
      ,um.`PAN`      
      ,um.`Pincode`      
      ,um.`Address`      
      ,um.`City`      
      ,um.`State`      
      ,um.`GSTNo`      
      ,um.`Status`      
      ,um.`CreatedOn`      
      ,um.`Password`      
      ,um.`Wallet_Limit`      
      ,um.`Available_Amount`      
      ,um.`Collection_Amount`      
      ,um.`ifsc`      
      ,um.`Account`      
      ,um.`Hold_Amt`      
      ,um.`SID`      
      ,um.`AgentID`      
      ,um.`payout_flag`      
      ,um.`Callback_URL`      
      ,um.`Payin_Chnl`      
      ,um.`Payout_Url`      
      ,um.`ClientID`      
      ,um.`ClientSecret`      
      ,um.`RTSettlementOn`      
      ,um.`FailedCount`      
   ,ut.Pay_Tax      
    ,ut.Tax      
     ,um.`ClientID` ,    
	 um.PayoutServiceID,
  um.Business_Type,um.Business_Category,um.Business_Sub_Category,um.WebAppURL,um.BankName,um.AccountHolderName,um.UpdatedWebhookFlag,  
  um.UpdatedWebhookCreationTime,um.UpdatedApiKeyFlag,um.UpdatedApiKeyCreationTime   
  FROM UserMaster um join User_Tax_Mst ut on  ut.UserID=um.UserId where um.UserId=v_UserId;        
 END;
END
$$

DROP PROCEDURE IF EXISTS `GET_USERNAME`;
CREATE PROCEDURE `GET_USERNAME`(
    IN v_ClientID VARCHAR(50),
    IN v_ClientSecret VARCHAR(50)
)
BEGIN
BEGIN    
 # added to prevent extra result sets from    
 # interfering with SELECT statements.  
     
    
    # Insert statements for procedure here    
 SELECT UserId as USERID FROM UserMaster WHERE ClientID=v_ClientID and  ClientSecret= v_ClientSecret and Status=1;  
END;
END
$$

DROP PROCEDURE IF EXISTS `GetAgentList`;
CREATE PROCEDURE `GetAgentList`(
    
)
BEGIN
BEGIN        
 # added to prevent extra result sets from        
 # interfering with SELECT statements.        
         
select ID,name from AgentMst where status=1;       
END;
END
$$

DROP PROCEDURE IF EXISTS `getAllBenificiary_ByAgentId`;
CREATE PROCEDURE `getAllBenificiary_ByAgentId`(
    IN v_AgentId VARCHAR(20),
    IN v_SenderId VARCHAR(20)
)
BEGIN
BEGIN  
 # added to prevent extra result sets from  
 # interfering with SELECT statements.  
   
  
  SELECT CardNo  
       ,AgentId  
      ,BeneName  
      ,BeneMobile  
      ,BankName  
   ,BranchName  
      ,Ifsc  
      ,AccountNo  
      ,Date_Of_Registration  
   ,Status,SenderId,Mode  
  FROM AddBeneficiary where AgentId=v_AgentId and SenderId=v_SenderId; 
END;
END
$$

DROP PROCEDURE IF EXISTS `getAllBenificiary_Data`;
CREATE PROCEDURE `getAllBenificiary_Data`(
    IN v_AgentId VARCHAR(20)
)
BEGIN
BEGIN  
 # added to prevent extra result sets from  
 # interfering with SELECT statements.  
   

 
  SELECT CardNo  
       ,user_order_id AgentId  
      ,BeneName  
      ,BeneMobile  
      ,c.BankName  
	,m.AMOUNT BranchName  
      ,c.Ifsc  
      ,c.AccountNo  AccountNo
      ,Date_Of_Registration  
   ,c.Status,SenderId  
   ,Mode
  FROM AddBeneficiary c inner join MONEY_TRANSFER m on c.AgentId=m.CREATED_BY  where ServiceVendor=v_AgentId order by TRANSACTIONID desc LIMIT 5;
END;
END
$$

DROP PROCEDURE IF EXISTS `getPaySprintBank`;
CREATE PROCEDURE `getPaySprintBank`(
    IN v_TransactionID VARCHAR(200)
)
BEGIN
BEGIN        
 # added to prevent extra result sets from        
 # interfering with SELECT statements.        
         
      select us.InnerPayinFlag as BankName from MONEY_TRANSFER_PAYIN tt join UserMaster us on tt.CREATED_BY = us.UserId   where (tt.TRANSACTIONID =v_TransactionID  or  tt.user_order_id =v_TransactionID);    
    
END;
END
$$

DROP PROCEDURE IF EXISTS `getPaySprintKey`;
CREATE PROCEDURE `getPaySprintKey`(
    IN v_userID INT
)
BEGIN
BEGIN    
 # added to prevent extra result sets from    
 # interfering with SELECT statements.    
     
SELECT     
      `ClientID`    
      ,`ClientSecrate`    
      ,`PaySprintKey`    
      ,`PaySprintIV`    
   ,VPAUser  
  FROM `PaySprintCred`  where `PaySprintCredID`=v_userID and `IsActive`=1;    
END;
END
$$

DROP PROCEDURE IF EXISTS `getPaySprintTransactionStatus`;
CREATE PROCEDURE `getPaySprintTransactionStatus`(
    IN v_TransactionID VARCHAR(200)
)
BEGIN
BEGIN      
 # added to prevent extra result sets from      
 # interfering with SELECT statements.      
       
SELECT       
      `ClientID`      
      ,`ClientSecrate`      
      ,`PaySprintKey`      
      ,`PaySprintIV`      
   ,VPAUser    
  FROM `PaySprintCred`  where PaySprintCredID=(select us.InnerPayinFlag from MONEY_TRANSFER_PAYIN tt join UserMaster us on tt.CREATED_BY = us.UserId   where (tt.TRANSACTIONID =v_TransactionID  or  tt.user_order_id =v_TransactionID)   ) and `IsActive`=1;      
END;
END
$$

DROP PROCEDURE IF EXISTS `GetUserID`;
CREATE PROCEDURE `GetUserID`(
    
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	
SELECT UserId
      ,UserId
       FROM UserMaster; #where Status=1
END;
END
$$

DROP PROCEDURE IF EXISTS `GetUserID_Agent`;
CREATE PROCEDURE `GetUserID_Agent`(
    IN v_AgentID INT
)
BEGIN
BEGIN    
 # added to prevent extra result sets from    
 # interfering with SELECT statements.    
     
SELECT UserId,UserId    
       FROM UserMaster  where AgentID=v_AgentID;  
END;
END
$$

DROP PROCEDURE IF EXISTS `GetUserList`;
CREATE PROCEDURE `GetUserList`(
    
)
BEGIN
BEGIN        
 # added to prevent extra result sets from        
 # interfering with SELECT statements.        
         
SELECT UserId        
      ,Name        
      ,BusinessName        
      ,Mobile        
      ,EmailId        
      ,Gender        
      ,Dob        
      ,PAN        
      ,Pincode        
      ,Address        
      ,City        
      ,State        
      ,GSTNo        
      ,Status        
      ,Password        
      ,Collection_Amount Wallet_Limit        
      ,Available_Amount        
  , Account,ifsc,Hold_Amt,sid,(select name from AgentMst where id=AgentID) AName  ,      
   payout_flag      
      ,Callback_URL      
      ,Payin_Chnl      
      ,Payout_Url      
      ,RTSettlementOn      
   ,FailedCount   
   ,PayoutServiceID  
  FROM UserMaster order by UserId desc; #where Status=1        
END;
END
$$

DROP PROCEDURE IF EXISTS `HoldAmount`;
CREATE PROCEDURE `HoldAmount`(
    IN v_flag INT,
    IN v_userID INT,
    IN v_Amount DECIMAL(10,2)
)
BEGIN
BEGIN  
 # added to prevent extra result sets from  
 # interfering with SELECT statements.  
   
  
IF ((v_flag=1)) THEN
  update `UserMaster` set `Hold_Amt` =  v_Amount  where `UserId`=v_userID;  
  
update `UserMaster` set `Collection_Amount` = `Collection_Amount` - v_Amount  where `UserId`=v_userID; 

INSERT INTO MONEY_TRANSFER_PAYIN  
           (SERVICE_NAME  
           ,AMOUNT  
           ,AccountNo  
           ,MobileNo  
           ,BENEFICIAL_ID  
           ,CREATED_ON  
           ,CREATED_BY  
           ,ServiceVendor  
           ,ModOfPayment  
     ,Tax   
     ,Tax_AMOUNT  
     ,Total_AMOUNT  
     ,user_order_id  
     ,BankName   
     ,IFSC  
     ,HolderName  
     ,Total_CD_Amt  
     ,TrFrom  
     ,TRSID,  
     REQUEST_IP_ADDRESS,  
     STATUS  
     )  
     VALUES('Holding Amount Refunded'  
           ,v_AMOUNT  
           ,''  
           ,''  
           ,''  
           ,DATE_ADD(CAST(NOW() AS DATETIME), INTERVAL 750 MINUTE)  
           ,v_userID  
           ,''  
           ,'Refund'  
     ,0   
     ,0  
     ,v_AMOUNT  
     ,CONCAT('Refund_',v_AMOUNT,'_',DATE_ADD(CAST(NOW() AS DATETIME), INTERVAL 750 MINUTE))  
     ,''   
     ,''  
     ,v_userID  
     ,'',  
      '',  
   '',  
     '',  
     'SUCCESS'  
     );
  END IF;  
IF ((v_flag=2)) THEN
  update `UserMaster` set `Hold_Amt` = `Hold_Amt` -v_Amount   where `UserId`=v_userID;  
update `UserMaster` set `Collection_Amount` = `Collection_Amount` + v_Amount  where `UserId`=v_userID; 
INSERT INTO MONEY_TRANSFER_PAYIN  
           (SERVICE_NAME  
           ,AMOUNT  
           ,AccountNo  
           ,MobileNo  
           ,BENEFICIAL_ID  
           ,CREATED_ON  
           ,CREATED_BY  
           ,ServiceVendor  
           ,ModOfPayment  
     ,Tax   
     ,Tax_AMOUNT  
     ,Total_AMOUNT  
     ,user_order_id  
     ,BankName   
     ,IFSC  
     ,HolderName  
     ,Total_CD_Amt  
     ,TrFrom  
     ,TRSID,  
     REQUEST_IP_ADDRESS,  
     STATUS  
     )  
     VALUES('Put Amount on hold'  
           ,v_AMOUNT  
           ,''  
           ,''  
           ,''  
           ,DATE_ADD(CAST(NOW() AS DATETIME), INTERVAL 750 MINUTE)  
           ,v_userID  
           ,''  
           ,'Hold'  
     ,0   
     ,0  
     ,v_AMOUNT  
     ,CONCAT('Hold_',v_AMOUNT,'_',DATE_ADD(CAST(NOW() AS DATETIME), INTERVAL 750 MINUTE))  
     ,''   
     ,''  
     ,v_userID  
     ,'',  
      '',  
   '',  
     '',  
     'SUCCESS'  
     );
  END IF;  
  
  
END;
END
$$

DROP PROCEDURE IF EXISTS `RefundBalanceTRANSACTIONID`;
CREATE PROCEDURE `RefundBalanceTRANSACTIONID`(
    
)
BEGIN
BEGIN  
 # added to prevent extra result sets from  
 # interfering with SELECT statements.  
   
SELECT  t.TRANSACTIONID,t.AMOUNT,t.CREATED_BY FROM MONEY_TRANSFER_PAYIN t where  t.STATUS='SUCCESS' and   t.ModOfPayment='Collection' and t.UpdatedBalance=0;  
END;
END
$$

DROP PROCEDURE IF EXISTS `SettlementtoWallet`;
CREATE PROCEDURE `SettlementtoWallet`(
    IN v_userID INT
)
BEGIN
BEGIN    
DECLARE v_CollectionAmount float;    
DECLARE v_UserTax float;    
DECLARE v_OurAmount float;    
DECLARE v_TransferAmount float;    
DECLARE v_rndNuber varchar(10);    
     
    
set v_CollectionAmount = (select Collection_Amount   from UserMaster  where UserId=v_userID);    
set v_UserTax = (select Tax   from User_Tax_Mst  where UserId=v_userID);    
set v_OurAmount = ((v_CollectionAmount * v_UserTax)/100);    
set v_TransferAmount = v_CollectionAmount - v_OurAmount;    
set v_rndNuber= CONCAT('S', (SELECT FLOOR(RAND() * (100000 - 1 + 1)) + 1));    
    
#select  v_CollectionAmount,v_UserTax,v_TransferAmount;    
update UserMaster  set Available_Amount = (Available_Amount + v_TransferAmount),Collection_Amount=0   where UserId= v_userID;    
    
 INSERT INTO MONEY_TRANSFER_PAYIN        
           (SERVICE_NAME        
           ,AMOUNT        
           ,AccountNo        
           ,MobileNo        
           ,BENEFICIAL_ID        
           ,CREATED_ON        
           ,CREATED_BY        
           ,ServiceVendor        
           ,ModOfPayment        
     ,Tax         
     ,Tax_AMOUNT        
     ,Total_AMOUNT        
     ,user_order_id        
     ,BankName         
     ,IFSC        
     ,HolderName        
     ,Total_CD_Amt        
     ,TrFrom        
     ,TRSID,        
     REQUEST_IP_ADDRESS,      
  STATUS,      
  UTR,      
  UPDATED_ON      
     )        
     VALUES(v_userID        
           ,v_CollectionAmount        
           ,''        
           ,'999999999'       
           ,''        
           ,NOW()        
           , v_userID    
           ,v_userID        
           ,'Settlement'        
     ,v_UserTax    
     ,v_OurAmount        
     ,v_TransferAmount        
     ,v_rndNuber       
     ,'NGMB SOFTWARE SOLUTION LLP'         
     ,''        
     ,'NGMB SOFTWARE SOLUTION LLP'        
     ,'' ,      
      '',        
   '',        
     '' ,      
  'SUCCESS',      
  '0',      
  NOW()      
     );      
    
    
END;
END
$$

DROP PROCEDURE IF EXISTS `Update_USER_BY_ID`;
CREATE PROCEDURE `Update_USER_BY_ID`(
    IN v_UserId INT,
    IN v_Status INT
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
	/****** Script for SelectTopNRows command from SSMS  ******/

  update UserMaster set Status=v_Status where UserId=v_UserId;
	END;
END
$$

DROP PROCEDURE IF EXISTS `updateBasicInfo`;
CREATE PROCEDURE `updateBasicInfo`(
    IN v_userID INT,
    IN v_Name VARCHAR(200),
    IN v_BusinessName VARCHAR(1000),
    IN v_MobileNumber BIGINT,
    IN v_EmailID VARCHAR(200),
    OUT v_loginStatus INT
)
BEGIN
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN

   SET v_loginStatus = 2;

  END;

	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

	

   UPDATE `UserMaster` set 
       Name = v_Name
      ,`BusinessName` = v_BusinessName
      ,`Mobile` = v_MobileNumber
      ,`EmailId` = v_EmailID
   WHERE UserId =v_userID;

   SET v_loginStatus = 1;



END;
END
$$

DROP PROCEDURE IF EXISTS `userPayinPayoutReport`;
CREATE PROCEDURE `userPayinPayoutReport`(
    
)
BEGIN
BEGIN  
   
SELECT CONCAT(BusinessName,' (',UserId,')' ) as BusinessName,  `Available_Amount`
      ,`Collection_Amount`,(Case when `Status` =1 then 'Active' else 'Deactive' end) as Status,`Hold_Amt` FROM `UserMaster`;
   
  
END;
END
$$

DROP PROCEDURE IF EXISTS `userPayoutReportDateWise`;
CREATE PROCEDURE `userPayoutReportDateWise`(
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10)
)
BEGIN
BEGIN
	
	select mk.totalAmount as total,CONCAT(ut.BusinessName,' (',mk.CREATED_BY,')' ) as BusinessName,mk.CREATED_ON as date from (select   SUM(mt.AMOUNT) as totalAmount,mt.CREATED_BY,DATE_FORMAT(mt.CREATED_ON, '%Y-%m-%dT%H:%i:%s') as CREATED_ON
		from MONEY_TRANSFER_PAYOUT mt  where 
		#and  IFNULL(UTR,'0')<>'0'
		 mt.STATUS in('paid','Success','processing','SUCCESS') 

		and  DATE_FORMAT(mt.CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate
		and DATE_FORMAT(mt.CREATED_ON, '%Y-%m-%dT%H:%i:%s')<= v_todate and mt.ModOfPayment in ('IMPS','NEFT','UPI') group by mt.CREATED_BY,DATE_FORMAT(mt.CREATED_ON, '%Y-%m-%dT%H:%i:%s')) mk join UserMaster ut on mk.CREATED_BY=ut.UserId;


	

END;
END
$$

DROP PROCEDURE IF EXISTS `userReportDateWise`;
CREATE PROCEDURE `userReportDateWise`(
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10)
)
BEGIN
BEGIN  
   
 select mk.totalAmount as total,CONCAT(ut.BusinessName,' (',mk.CREATED_BY,')' ) as BusinessName,mk.CREATED_ON as date from (select   SUM(mt.AMOUNT) as totalAmount,mt.CREATED_BY,DATE_FORMAT(mt.CREATED_ON, '%Y-%m-%dT%H:%i:%s') as CREATED_ON  
  from MONEY_TRANSFER_PAYIN mt  where   
  #and  IFNULL(UTR,'0')<>'0'  
   mt.STATUS in('paid','Success','processing','SUCCESS')   
  
  and  DATE_FORMAT(mt.CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate  
  and DATE_FORMAT(mt.CREATED_ON, '%Y-%m-%dT%H:%i:%s')<= v_todate  and mt.ModOfPayment in ('collection')  group by mt.CREATED_BY,DATE_FORMAT(mt.CREATED_ON, '%Y-%m-%dT%H:%i:%s')) mk join UserMaster ut on mk.CREATED_BY=ut.UserId;  
  
  
   
  
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_ADD_UPDATE_AMOUNT_WALLET`;
CREATE PROCEDURE `USP_ADD_UPDATE_AMOUNT_WALLET`(
    IN v_UserId INT,
    IN v_Available_Amount DECIMAL(10,2)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
	#Begin Try
	#UPDATE UserMaster
 #  SET Available_Amount = Available_Amount+v_Available_Amount
 #WHERE UserId=v_UserId
	#select 'success' as message,0 status;
	#END try
	#Begin Catch
	#select 'failed' as message,1 status;
	#End Catch
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_ADD_UPDATE_AMOUNT_WALLETBYADMIN`;
CREATE PROCEDURE `USP_ADD_UPDATE_AMOUNT_WALLETBYADMIN`(
    IN v_UserId INT,
    IN v_Available_Amount DECIMAL(18,0),
    IN v_TRANSACTIONID LONGTEXT
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
	
	update MONEY_TRANSFER SET STATUS='Success' ,Total_CD_Amt=(select Available_Amount+v_Available_Amount from UserMaster WHERE UserId=v_UserId)
	where TRANSACTIONID=cast(v_TRANSACTIONID AS SIGNED) and PortalName='Request For Add Amount';

	UPDATE UserMaster
   SET Available_Amount =Available_Amount+v_Available_Amount
 WHERE UserId=v_UserId;
	 #
END;
END
$$

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

DROP PROCEDURE IF EXISTS `USP_ADD_USER_API_DETAILS`;
CREATE PROCEDURE `USP_ADD_USER_API_DETAILS`(
    IN v_APIID INT,
    IN v_UserId INT,
    IN v_ServiceName VARCHAR(20),
    IN v_CreatedBy INT,
    IN v_CreatedOn DATETIME
)
BEGIN
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN

		select 'FAILLED' as message,1 STATUS;
	
  END;

	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
	

	INSERT INTO `UserAPIDetails`
           (`APIID`
           ,`UserId`
           ,`ServiceName`
           ,`CreatedBy`
           ,`CreatedOn`
           )
     VALUES
           (v_APIID 
           ,v_UserId
           ,v_ServiceName 
           ,v_CreatedBy
           ,v_CreatedOn
           )

	;select 'SUCCESS' as message,0 STATUS;
	
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_Admin_CHANGE_PASSWORD_1`;
CREATE PROCEDURE `USP_Admin_CHANGE_PASSWORD_1`(
    IN v_UserId INT,
    IN v_Password VARCHAR(50),
    IN v_OldPassword VARCHAR(50),
    OUT v_loginStatus INT
)
BEGIN
BEGIN


	
	DECLARE v_oldPasswordInner varchar(50);
	set v_oldPasswordInner = (select password  from AdminMst  where AdminUserID=v_UserId);
    # Insert statements for procedure here
	IF ((v_oldPasswordInner = v_OldPassword)) THEN
  UPDATE AdminMst
   SET password = v_Password
 WHERE AdminUserID=v_UserId
	
  ;SET v_loginStatus = 1;
  ELSE
  IF ((v_oldPasswordInner != v_OldPassword)) THEN
    SET v_loginStatus = 2;
    ELSE
    SET v_loginStatus = 3;
    END IF;
  END IF;END;
END
$$

DROP PROCEDURE IF EXISTS `USP_AGENT_CHANGE_PASSWORD`;
CREATE PROCEDURE `USP_AGENT_CHANGE_PASSWORD`(
    IN v_UserId INT,
    IN v_Password VARCHAR(50),
    IN v_OldPassword VARCHAR(50),
    OUT v_loginStatus INT
)
BEGIN
BEGIN


	
	DECLARE v_oldPasswordInner varchar(50);
	set v_oldPasswordInner = (select pwd  from AgentMst  where ID=v_UserId);
    # Insert statements for procedure here
	IF ((v_oldPasswordInner = v_OldPassword)) THEN
  UPDATE AgentMst
   SET pwd = v_Password
 WHERE ID=v_UserId
	
  ;SET v_loginStatus = 1;
  ELSE
  IF ((v_oldPasswordInner != v_OldPassword)) THEN
    SET v_loginStatus = 2;
    ELSE
    SET v_loginStatus = 3;
    END IF;
  END IF;END;
END
$$

DROP PROCEDURE IF EXISTS `USP_AGENT_CHANGE_PASSWORD_WITH_OLD`;
CREATE PROCEDURE `USP_AGENT_CHANGE_PASSWORD_WITH_OLD`(
    IN v_UserId INT,
    IN v_Password VARCHAR(50),
    IN v_oldPassword VARCHAR(100),
    OUT v_outFlag INT
)
BEGIN
DECLARE v_OldPasswordInner varchar(100);
BEGIN

	# added to prevent extra result sets from
	# interfering with SELECT statements.
	
	set v_OldPasswordInner =(select a.pwd from AgentMst a where a.login_id=v_UserId) 
	;IF ((v_OldPasswordInner = v_oldPassword)) THEN
  UPDATE AgentMst
     SET pwd = v_Password
     WHERE login_id=v_UserId;
	 SET v_outFlag = 0;
  ELSE
  SET v_outFlag = 1;
  END IF;
    # Insert statements for procedure here
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_AGENT_LOGIN`;
CREATE PROCEDURE `USP_AGENT_LOGIN`(
    IN v_UserId VARCHAR(20),
    IN v_Password VARCHAR(20)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
	
	 select id,Name
    ,login_id
	,status as loginStatus
	from AgentMst
		   where 
			login_id=v_UserId and
           pwd=v_Password and
		   Status=1;
		  
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_API_DETAILS`;
CREATE PROCEDURE `USP_API_DETAILS`(
    IN v_APIName VARCHAR(20),
    IN v_AuthKey VARCHAR(256),
    IN v_AuthDomain VARCHAR(300),
    IN v_Status INT,
    IN v_APICharges INT,
    IN v_CreatedOn DATETIME,
    IN v_CompanyName VARCHAR(200),
    IN v_OwnerName VARCHAR(200),
    IN v_Address VARCHAR(300),
    IN v_SharedKey VARCHAR(200),
    IN v_VerificationURL VARCHAR(200),
    IN v_TransactionURL VARCHAR(200)
)
BEGIN
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN

		select 'FAILLED' as message,1 STATUS;
	
  END;

	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
	

	INSERT INTO `APIMaster`
           (`APIName`
           ,`AuthKey`
           ,`AuthDomain`
           ,`Status`
           ,`APICharges`
           ,`CreatedOn`
           ,`CompanyName`
           ,`OwnerName`
           ,`Address`
           ,`SharedKey`
           ,`VerificationURL`
           ,`TransactionURL`)
     VALUES
           (v_APIName 
           ,v_AuthKey 
           ,v_AuthDomain
           ,v_Status 
           ,v_APICharges
           ,v_CreatedOn 
           ,v_CompanyName
           ,v_OwnerName 
           ,v_Address
           ,v_SharedKey
           ,v_VerificationURL
           ,v_TransactionURL)

	;select 'SUCCESS' as message,0 STATUS;
	
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_CHECK_SMS_COLL`;
CREATE PROCEDURE `USP_CHECK_SMS_COLL`(
    IN v_mobile DECIMAL(10,0),
    IN v_email VARCHAR(100),
    IN v_otp INT,
    IN v_userid INT,
    IN v_order_id LONGTEXT
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	
	DECLARE v_cnt int;
    # Insert statements for procedure here
	SELECT COUNT(1) INTO v_cnt FROM sms_order WHERE mobile=v_mobile AND userid=v_userid AND CAST(create_on AS date)=CAST(NOW() AS date) 
	and otp=v_otp and order_id=v_order_id LIMIT 1;
	IF (v_cnt>0) THEN
  update sms_order set send_st=v_cnt where order_id=v_order_id and userid=v_userid and otp=v_otp and CAST(create_on AS date)=CAST(NOW() AS date);
		   SELECT 'TRUE' TRANSACTIONID;
  ELSE
  SELECT 'we are unable to proccess this request.' TRANSACTIONID;
  END IF;
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_CHECK_UNIQUE_ID`;
CREATE PROCEDURE `USP_CHECK_UNIQUE_ID`(
    IN v_UserID VARCHAR(100),
    IN v_User_Order_ID VARCHAR(100)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Select statements for procedure here
	SELECT Tax status,'' message  FROM User_Tax_Mst where UserID=v_UserID;
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_CHECKAUTH_USER_ID_PASSWORD`;
CREATE PROCEDURE `USP_CHECKAUTH_USER_ID_PASSWORD`(
    IN v_UserId VARCHAR(100),
    IN v_Password VARCHAR(100),
    IN v_IpAddress VARCHAR(100)
)
BEGIN
BEGIN              
      
         
 DECLARE v_IpCount varchar(50);    
 DECLARE v_userIDInner int;  
        
  # select v_UserId;  
  
  
 set v_userIDInner = (select count(us.UserId)  from UserMaster as us where  us.IPWhilteListed=1 and  us.ClientID=v_UserId);  

IF (v_userIDInner > 0) THEN
  set v_IpCount  = (select count(p.WhitelistIPId)  from IpWhitelistMst as p  where p.WhitelistIP=v_IpAddress and p.UserID=(select us.UserId  from UserMaster as us where us.ClientID=v_UserId ));        
IF ((v_IpCount > 0)) THEN
    select up.ClientSecret  from UserMaster as up  where   up.ClientID=v_UserId and  up.ClientSecret=v_Password and up.Status=1;
    ELSE
    select '' as ClientSecret   from UserMaster as  up   where up.ClientID=v_UserId and  up.ClientSecret=v_Password and up.Status=1;
    END IF;
  ELSE
  select '' as ClientSecret  from UserMaster as  up   where up.ClientID=v_UserId and  up.ClientSecret=v_Password and up.Status=1;
  END IF;     
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_COLLECTION_AMOUNT`;
CREATE PROCEDURE `USP_COLLECTION_AMOUNT`(
    IN v_UserId INT,
    IN v_Available_Amount DECIMAL(18,0),
    IN v_TRANSACTIONID VARCHAR(50)
)
BEGIN
BEGIN      
 DECLARE v_amount int;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    
     
  END;
      
       
       
 #select v_amount=Collection_Amount from UserMaster WHERE UserId=v_UserId      
    # Insert statements for procedure here      
 #If v_amount>=100      
 #Begin   
   
   
 UPDATE UserMaster      
   SET Collection_Amount =(IFNULL(Collection_Amount,0)+v_Available_Amount)      
 WHERE UserId=v_UserId ;  
   
  
      
 UPDATE MONEY_TRANSFER_PAYIN SET UpdatedBalance=1, Total_CD_Amt=(select Collection_Amount from UserMaster where UserId=v_UserId)      
    WHERE TRANSACTIONID=v_TRANSACTIONID      
 ;select 1 as status,'1' message;  
   
            
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_COLLECTION_AMOUNT_DIDUCT`;
CREATE PROCEDURE `USP_COLLECTION_AMOUNT_DIDUCT`(
    IN v_UserId INT,
    IN v_Available_Amount DECIMAL(18,0),
    IN v_TRANSACTIONID VARCHAR(50)
)
BEGIN
BEGIN
	DECLARE v_amount int;
	
	
	#select v_amount=Collection_Amount from UserMaster WHERE UserId=v_UserId
    # Insert statements for procedure here
	#If	v_amount>=100
	#Begin
	UPDATE UserMaster
   SET Collection_Amount =(IFNULL(Collection_Amount,0)-v_Available_Amount)
 WHERE UserId=v_UserId

 ;UPDATE MONEY_TRANSFER SET Total_CD_Amt=(select Collection_Amount from UserMaster where UserId=v_UserId)
				WHERE TRANSACTIONID=v_TRANSACTIONID
	;select 1 as status,'1' message;
	
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_COLLECTION_AMOUNT_PG`;
CREATE PROCEDURE `USP_COLLECTION_AMOUNT_PG`(
    IN v_UserId INT,
    IN v_Available_Amount DECIMAL(18,0),
    IN v_TRANSACTIONID VARCHAR(50)
)
BEGIN
BEGIN        
 DECLARE v_amount int;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
      
     
  END;
        
         
         
 #select v_amount=Collection_Amount from UserMaster WHERE UserId=v_UserId        
    # Insert statements for procedure here        
 #If v_amount>=100        
 #Begin     
   
     
 UPDATE UserMaster        
   SET Collection_Amount =(IFNULL(Collection_Amount,0)+v_Available_Amount)        
 WHERE UserId=v_UserId ;    
     
    
        
 UPDATE MONEY_TRANSFER_PAYIN_PG SET UpdatedBalance=1, Total_CD_Amt=(select Collection_Amount from UserMaster where UserId=v_UserId)        
    WHERE TRANSACTIONID=v_TRANSACTIONID        
 ;select 1 as status,'1' message;    
     
              
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_COLLECTION_TRANS_CHECK`;
CREATE PROCEDURE `USP_COLLECTION_TRANS_CHECK`(
    IN v_TRANSACTIONID VARCHAR(50)
)
BEGIN
BEGIN  
 # added to prevent extra result sets from  
 # interfering with SELECT statements.  
           
 SELECT   
   IFNULL(STATUS,'0') STATUS,  
      IFNULL(UTR,'0') Name  
  , CAST(user_order_id AS CHAR) TRANSACTIONID  
      ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER  
      ,(select IFNULL(Callback_URL,'0') from UserMaster where UserId=CREATED_BY) SERVICE_NAME  
   ,ModOfPayment  
      ,AMOUNT  
      ,TAX  
      ,TAX_AMOUNT  
      ,TOTAL_AMOUNT  
      ,AccountNo  
      ,MobileNo  
   , CREATED_ON  
      ,CREATED_BY  
      FROM MONEY_TRANSFER_PAYIN as MONEY_TRANSFER_RES  
 WHERE TRANSACTIONID=v_TRANSACTIONID;  
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_COLLECTION_TRANS_CHECK_PG`;
CREATE PROCEDURE `USP_COLLECTION_TRANS_CHECK_PG`(
    IN v_TRANSACTIONID VARCHAR(50)
)
BEGIN
BEGIN        
 # added to prevent extra result sets from        
 # interfering with SELECT statements.        
                 
 SELECT         
   IFNULL(STATUS,'0') STATUS,        
      IFNULL(UTR,'0') Name        
  , CAST(user_order_id AS CHAR) TRANSACTIONID        
      ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER        
      ,(select IFNULL(Callback_URL,'0') from UserMaster where UserId=CREATED_BY) SERVICE_NAME        
   ,ModOfPayment        
      ,AMOUNT        
      ,TAX        
      ,TAX_AMOUNT        
      ,TOTAL_AMOUNT        
      ,AccountNo        
      ,MobileNo        
   , CREATED_ON        
      ,CREATED_BY   ,ReturnURL  
	   ,(select IFNULL(CCAvenueWorkingKey,'') from UserMaster where UserId=CREATED_BY) privateKey 
	    ,(select IFNULL(RequestHashKey,'') from UserMaster where UserId=CREATED_BY) requtHash
      FROM MONEY_TRANSFER_PAYIN_PG as MONEY_TRANSFER_RES        
 WHERE TRANSACTIONID=v_TRANSACTIONID;        
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_COLLECTION_TRANS_UPDATE`;
CREATE PROCEDURE `USP_COLLECTION_TRANS_UPDATE`(
    IN v_REFERENCE_NUMBER VARCHAR(50),
    IN v_TRANSACTIONID VARCHAR(50),
    IN v_STATUS VARCHAR(30),
    IN v_UTR VARCHAR(20),
    IN v_Amount VARCHAR(20)
)
BEGIN
BEGIN  
 #today comment  
  UPDATE MONEY_TRANSFER SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS=v_STATUS,UPDATED_ON=NOW(),UTR=v_UTR,AMOUNT=v_Amount WHERE TRANSACTIONID=v_TRANSACTIONID  
    
 ;SELECT CAST(user_order_id AS CHAR) as TRANSACTIONID  
      ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER  
      ,SERVICE_NAME  
   ,ModOfPayment  
      ,AMOUNT  
      ,TAX  
      ,TAX_AMOUNT  
      ,TOTAL_AMOUNT  
      ,AccountNo  
      ,MobileNo  
  ,  STATUS  
      ,CREATED_ON  
      ,CREATED_BY  
   ,UTR  
      ,'' Name  
        
       FROM MONEY_TRANSFER as MONEY_TRANSFER_RES  
 WHERE TRANSACTIONID=v_TRANSACTIONID;  
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_COLLECTION_TRANS_UPDATE_FRM_EXL`;
CREATE PROCEDURE `USP_COLLECTION_TRANS_UPDATE_FRM_EXL`(
    IN v_REFERENCE_NUMBER VARCHAR(50),
    IN v_TRANSACTIONID VARCHAR(50)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	
    #      if v_STATUS='paid'
		  #begin
				#UPDATE MONEY_TRANSFER SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS=v_STATUS,UPDATED_ON=NOW(),UTR=v_UTR
				#,Total_CD_Amt=(select Collection_Amount from UserMaster where UserId='10021')
				#WHERE TRANSACTIONID=v_TRANSACTIONID
		  #end
		  #else
		  #Begin
			UPDATE MONEY_TRANSFER SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS='Success',UPDATED_ON=NOW(),UTR=v_REFERENCE_NUMBER WHERE TRANSACTIONID=v_TRANSACTIONID;
		 # End
	#SELECT CAST(user_order_id AS CHAR) TRANSACTIONID
 #     ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER
 #     ,SERVICE_NAME
	#  ,ModOfPayment
 #     ,AMOUNT
 #     ,TAX
 #     ,TAX_AMOUNT
 #     ,TOTAL_AMOUNT
 #     ,AccountNo
 #     ,MobileNo
	# , 'Success'  STATUS
 #     ,CREATED_ON
 #     ,CREATED_BY
	#  ,UTR
 #     ,(SELECT BeneName FROM AddBeneficiary WHERE CardNo=MONEY_TRANSFER_RES.BENEFICIAL_ID) Name
      
 #      FROM MONEY_TRANSFER as MONEY_TRANSFER_RES
	#WHERE TRANSACTIONID=v_TRANSACTIONID
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_COLLECTION_TRANS_UPDATE_SB`;
CREATE PROCEDURE `USP_COLLECTION_TRANS_UPDATE_SB`(
    IN v_REFERENCE_NUMBER VARCHAR(50),
    IN v_TRANSACTIONID VARCHAR(50),
    IN v_STATUS VARCHAR(30),
    IN v_UTR VARCHAR(20)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	
    #      if v_STATUS='paid'
		  #begin
				#UPDATE MONEY_TRANSFER SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS=v_STATUS,UPDATED_ON=NOW(),UTR=v_UTR
				#,Total_CD_Amt=(select Collection_Amount from UserMaster where UserId='10021')
				#WHERE TRANSACTIONID=v_TRANSACTIONID
		  #end
		  #else
		  #Begin
			UPDATE MONEY_TRANSFER SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS=v_STATUS,UPDATED_ON=NOW(),UTR=v_UTR WHERE TRANSACTIONID=v_TRANSACTIONID;
		 # End
	#SELECT CAST(user_order_id AS CHAR) TRANSACTIONID
 #     ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER
 #     ,SERVICE_NAME
	#  ,ModOfPayment
 #     ,AMOUNT
 #     ,TAX
 #     ,TAX_AMOUNT
 #     ,TOTAL_AMOUNT
 #     ,AccountNo
 #     ,MobileNo
	# , 'Success'  STATUS
 #     ,CREATED_ON
 #     ,CREATED_BY
	#  ,UTR
 #     ,(SELECT BeneName FROM AddBeneficiary WHERE CardNo=MONEY_TRANSFER_RES.BENEFICIAL_ID) Name
      
 #      FROM MONEY_TRANSFER as MONEY_TRANSFER_RES
	#WHERE TRANSACTIONID=v_TRANSACTIONID
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_COLLECTION_TRANS_UPDATE1`;
CREATE PROCEDURE `USP_COLLECTION_TRANS_UPDATE1`(
    IN v_REFERENCE_NUMBER VARCHAR(50),
    IN v_TRANSACTIONID VARCHAR(50),
    IN v_STATUS VARCHAR(30),
    IN v_UTR VARCHAR(20)
)
BEGIN
BEGIN
	#today comment
		#UPDATE MONEY_TRANSFER SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS=v_STATUS,UPDATED_ON=NOW() WHERE TRANSACTIONID=v_TRANSACTIONID
		
	SELECT CAST(user_order_id AS CHAR) as TRANSACTIONID
      ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER
      ,SERVICE_NAME
	  ,ModOfPayment
      ,AMOUNT
      ,TAX
      ,TAX_AMOUNT
      ,TOTAL_AMOUNT
      ,AccountNo
      ,MobileNo
	 , (case when STATUS='paid' then 'Success' else STATUS end) STATUS
      ,CREATED_ON
      ,CREATED_BY
	  ,UTR
      ,(SELECT BeneName FROM AddBeneficiary WHERE CardNo=MONEY_TRANSFER_RES.BENEFICIAL_ID) Name
      
       FROM MONEY_TRANSFER as MONEY_TRANSFER_RES
	WHERE TRANSACTIONID=v_TRANSACTIONID;
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_CRUD_API_KEY`;
CREATE PROCEDURE `USP_CRUD_API_KEY`(
    IN v_flag INT,
    IN v_UserID INT,
    IN v_ClientID VARCHAR(100),
    IN v_ClientSecret VARCHAR(100),
    IN v_RequestHashKey VARCHAR(100),
    IN v_RequestSaltKey VARCHAR(100),
    IN v_RequestAESKey VARCHAR(100),
    IN v_ResponseHashKey VARCHAR(100),
    IN v_ResponseSaltKey VARCHAR(100),
    IN v_ResponseAESKey VARCHAR(100),
    OUT v_loginStatus INT
)
BEGIN
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN

   SET v_loginStatus = 2;

  END;

	# added to prevent extra result sets from
	# interfering with SELECT statements.
	
	 IF ((v_flag=1)) THEN
  SELECT  `ClientID`
      ,`ClientSecret`
	   ,`RequestHashKey`
      ,`RequestSaltKey`
      ,`RequestAESKey`
      ,`ResponseHashKey`
      ,`ResponseSaltKey`
      ,`ResponseAESKey`,
	  Callback_URL,
	  Payout_Url,
	  UpdatedWebhookCreationTime,
	  UpdatedWebhookCreationTime,
	  UpdatedWebhookFlag,
	  UpdatedApiKeyFlag
	  
	  
  FROM UserMaster  where `UserID` =v_UserID ;
  ELSE
  IF ((v_flag=2)) THEN
    UPDATE UserMaster
   SET `ClientID` =v_ClientID
      ,`ClientSecret`=v_ClientSecret
	   ,`RequestHashKey`=v_RequestHashKey
      ,`RequestSaltKey`=v_RequestSaltKey
      ,`RequestAESKey`=v_RequestAESKey
      ,`ResponseHashKey`=v_ResponseHashKey
      ,`ResponseSaltKey`=v_ResponseSaltKey
      ,`ResponseAESKey`=v_ResponseAESKey,
	    UpdatedApiKeyFlag = 1,
      UpdatedApiKeyCreationTime= DATE_ADD(CAST(NOW() AS DATETIME), INTERVAL 750 MINUTE)
    
 WHERE  `UserID` =v_UserID ; 
	 SET v_loginStatus = 1;
    END IF;
  END IF;

END;
END
$$

DROP PROCEDURE IF EXISTS `USP_CRUD_IP_WHITELIST`;
CREATE PROCEDURE `USP_CRUD_IP_WHITELIST`(
    IN v_flag INT,
    IN v_WhitelistIPId INT,
    IN v_UserID INT,
    IN v_WhitelistIP VARCHAR(100),
    OUT v_loginStatus INT
)
BEGIN
BEGIN    
DECLARE v_IPcheck int;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    
   SET v_loginStatus = 2;    

  END;

     
  IF ((v_flag=1)) THEN
  SELECT  `WhitelistIPId`    
      ,`WhitelistIP`    
      ,`UserID`    
      ,`Created_On`    
      ,`Upated_On`    
      ,`Status`    
  FROM `IpWhitelistMst`  where `UserID` =v_UserID ;
  ELSE
  IF ((v_flag=2)) THEN
    set v_IPcheck = (select count(*) from `IpWhitelistMst`  where WhitelistIP=v_WhitelistIP and UserID=v_UserID);
 IF ((v_IPcheck=0)) THEN
      INSERT INTO `IpWhitelistMst`    
           (WhitelistIP,    
             UserID    
           )    
     VALUES    
           (    
       v_WhitelistIP ,  
          v_UserID    
         )    
    ;SET v_loginStatus = 1;
      ELSE
      SET v_loginStatus = 4;
      END IF;
    ELSE
    IF ((v_flag=3)) THEN
      UPDATE `IpWhitelistMst`    
   SET `WhitelistIP` =  v_WhitelistIP     
        
 WHERE WhitelistIPId=v_WhitelistIPId and UserID =v_UserID;    
  SET v_loginStatus = 1;
      ELSE
      IF ((v_flag=4)) THEN
        DELETE FROM `IpWhitelistMst`    
     where WhitelistIPId=v_WhitelistIPId and UserID =v_UserID;    
  SET v_loginStatus = 1;
        END IF;
      END IF;
    END IF;
  END IF;    
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_CRUD_WEB_HOOK`;
CREATE PROCEDURE `USP_CRUD_WEB_HOOK`(
    IN v_flag INT,
    IN v_UserID INT,
    IN v_Callback_URL VARCHAR(1000),
    IN v_Payout_Url VARCHAR(1000),
    OUT v_loginStatus INT
)
BEGIN
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN

   SET v_loginStatus = 2;

  END;

	# added to prevent extra result sets from
	# interfering with SELECT statements.
	
	 IF ((v_flag=1)) THEN
  SELECT  Callback_URL
      ,Payout_Url
      
  FROM `UserMaster`  where UserId =v_UserID ;
  ELSE
  IF ((v_flag=3)) THEN
    UPDATE `UserMaster`
   SET Callback_URL =  v_Callback_URL,
   Payout_Url =  v_Payout_Url,
   UpdatedWebhookFlag = 1,
   UpdatedWebhookCreationTime= DATE_ADD(CAST(NOW() AS DATETIME), INTERVAL 750 MINUTE)
      where UserId =v_UserID ;
	 SET v_loginStatus = 1;
    ELSE
    IF ((v_flag=4)) THEN
      UPDATE `UserMaster`
   SET Callback_URL =  '',
   Payout_Url =  ''
      where UserId =v_UserID ;
	 SET v_loginStatus = 1;
      END IF;
    END IF;
  END IF;
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_GET_API_KEY`;
CREATE PROCEDURE `USP_GET_API_KEY`(
    IN v_ClientID VARCHAR(100),
    IN v_ClientSecret VARCHAR(100)
)
BEGIN
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN

   select '';

  END;



 SELECT  `ClientID`
      ,`ClientSecret`
	   ,`RequestHashKey`
      ,`RequestSaltKey`
      ,`RequestAESKey`
      ,`ResponseHashKey`
      ,`ResponseSaltKey`
      ,`ResponseAESKey`
	
	  
	  
  FROM UserMaster  where ClientID =v_ClientID and  ClientSecret=v_ClientSecret ; 

END;
END
$$

DROP PROCEDURE IF EXISTS `USP_GET_API_LIST`;
CREATE PROCEDURE `USP_GET_API_LIST`(
    
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
	SELECT APIID
      ,APIName
      ,AuthKey
      ,AuthDomain
      ,Status
      ,APICharges
      ,CreatedOn
      ,CompanyName
      ,OwnerName
      ,Address
      ,SharedKey
      ,VerificationURL
      ,TransactionURL
  FROM APIMaster as api_Masters;
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_GET_Collection_FOR_status_check`;
CREATE PROCEDURE `USP_GET_Collection_FOR_status_check`(
    IN v_fromdate VARCHAR(30),
    IN v_todate VARCHAR(20)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
select  TRANSACTIONID,amount,CREATED_ON,status,UTR,CREATED_BY,CREATED_ON#,IFNULL(REFERENCE_NUMBER,0) REFERENCE_NUMBER,utr 
from MONEY_TRANSFER where CREATED_BY='11060' and 
ModOfPayment='collection' and IFNULL(STATUS,'0')='0' 
and cast(CREATED_ON as date)='2023-10-01';
#and TRANSACTIONID not in(1083205)

end;
END
$$

DROP PROCEDURE IF EXISTS `USP_Get_Message`;
CREATE PROCEDURE `USP_Get_Message`(
    
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

  SELECT `NotificationID`
      ,`Text`
      ,`Created_at`
  FROM `Notifications`  order by `Created_at` desc LIMIT 30;
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_GET_PASSWORD`;
CREATE PROCEDURE `USP_GET_PASSWORD`(
    IN v_userID INT,
    IN v_Adminpassword VARCHAR(200)
)
BEGIN
BEGIN
	DECLARE v_CountPassword int;
	

	set v_CountPassword = (select count(*) from AdminMst  where password=v_Adminpassword);
	IF ((v_CountPassword > 0)) THEN
  select Password from UserMaster  where UserId= v_userID and UserType=1;
  ELSE
  select '' as Password;
  END IF;
  
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_GET_PayOut_FOR_status_check`;
CREATE PROCEDURE `USP_GET_PayOut_FOR_status_check`(
    IN v_fromdate VARCHAR(30),
    IN v_todate VARCHAR(20)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
select TRANSACTIONID,user_order_id REFERENCE_NUMBER,utr from MONEY_TRANSFER where 
ModOfPayment not in('Settlement','collection','charge back')
and cast(CREATED_ON as date)='2023-09-15' #and PortalName is null 
and CREATED_BY in('11057')
#and CREATED_BY='10022' 
#and utr is null
#and STATUS='created'
and IFNULL(utr,'0')<>'0';
		  
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_GET_USER_ID_PASSWORD`;
CREATE PROCEDURE `USP_GET_USER_ID_PASSWORD`(
    IN v_UserId VARCHAR(200),
    IN v_Password VARCHAR(500),
    IN v_userType INT
)
BEGIN
BEGIN    
     
     
    
    # Insert statements for procedure here    
     
  select CAST(UserId AS CHAR) as loginId    
      ,Name    
     ,Name BusinessName     
     ,Mobile    
    , Available_Amount ,IFNULL(Collection_Amount,0) Collection_Amount    
 ,IFNULL(ifsc,'0') ifsc,IFNULL(account,'0') account,case when UserId in('11049','11054','10046','11050','11057','11053','11070','10045','10025','10026')then 0 else (case when v_UserId in('11055','10022') then  300000 else case when v_UserId in('11056') then
  
  1000000 else IFNULL(Hold_Amt,0) end  end )end Hold_Amt ,  
  Status as loginStatus,  
  EmailId  
 from UserMaster as UserMasters    
     where     
   EmailId=v_UserId and    
         #  Password=cast(DecryptByPassPhrase( 'NGMBv_234556LoginTest',v_Password) AS CHAR) and    
     Password=v_Password and    
     Status=1    
	 and UserType =1;
        
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_GET_USER_ID_PASSWORD_1_Admin`;
CREATE PROCEDURE `USP_GET_USER_ID_PASSWORD_1_Admin`(
    IN v_UserId VARCHAR(200),
    IN v_Password VARCHAR(500),
    OUT v_loginStatus INT,
    OUT v_usetID INT
)
BEGIN
BEGIN    
     
     
    
    # Insert statements for procedure here    
     
  SELECT `AdminUserID`, `status` INTO v_usetID, v_loginStatus FROM `AdminMst`
     where     
   userName=v_UserId and    
         
     password=v_Password and    
    status=1 LIMIT 1;END;
END
$$

DROP PROCEDURE IF EXISTS `USP_GET_WALLET_AMOUNT`;
CREATE PROCEDURE `USP_GET_WALLET_AMOUNT`(
    IN v_UserId INT
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
	select Available_Amount from UserMaster as availableBalances
 WHERE UserId=v_UserId;
	
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_GROUP_Payin_Revenue`;
CREATE PROCEDURE `USP_GROUP_Payin_Revenue`(
    IN v_CREATED_BY VARCHAR(10),
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10)
)
BEGIN
BEGIN              
               
              
              
/*DECLARE v_total_volume numeric(10,2);              
DECLARE v_total_fees numeric(10,2);              
DECLARE v_total_tax numeric(10,2);              
DECLARE v_success_ratio numeric(10,2);              
DECLARE v_total_transactions int;              
DECLARE v_success int;              
DECLARE v_failed int;              
DECLARE v_Cancelled int;              
DECLARE v_payout_total_transactions int;              
DECLARE v_ayout_success int;              
DECLARE v_payout_failed int;              
DECLARE v_Cpayout_pending int;              
DECLARE v_successRatio int;              
              
set v_total_volume =(select sum(TOTAL_AMOUNT)               
  from MONEY_TRANSFER_PAYIN where CREATED_BY=v_CREATED_BY and ModOfPayment in('collection')              
  and CREATED_BY =v_CREATED_BY              
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate              
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate              
 order by UPDATED_ON desc);              
 set v_successRatio  =(select sum(TRANSACTIONID)               
  from MONEY_TRANSFER_PAYIN where CREATED_BY=v_CREATED_BY and ModOfPayment in('collection')              
  and CREATED_BY =v_CREATED_BY              
  and  STATUS in('paid','Success','processing','SUCCESS')              
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate              
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate              
 order by UPDATED_ON desc)              
set v_total_fees  =(select sum(TAX)               
  from MONEY_TRANSFER_PAYIN where CREATED_BY=v_CREATED_BY and ModOfPayment in('collection')              
  #and  IFNULL(UTR,'0')<>'0'              
  and STATUS in('paid','Success','processing','SUCCESS')               
   and CREATED_BY =v_CREATED_BY              
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate              
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate              
 order by UPDATED_ON desc);              
set v_total_tax  =(select sum(TAX)               
  from MONEY_TRANSFER_PAYIN where CREATED_BY=v_CREATED_BY and ModOfPayment in('collection')              
  #and  IFNULL(UTR,'0')<>'0'              
  and STATUS in('paid','Success','processing','SUCCESS')               
   and CREATED_BY =v_CREATED_BY              
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate              
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate              
 order by UPDATED_ON desc)              
set v_success_ratio  = (v_successRatio * 100) / v_total_volume ;              
              
              
*/       
      
IF ((v_CREATED_BY !=0)) THEN
  select              
  IFNULL(sum(case when STATUS in('paid','Success','processing','SUCCESS') then TAX else 0 end),0) as total_fees,              
  IFNULL( sum(case when ModOfPayment not in('Settlement','Refund','Chargeback','Hold') then TOTAL_AMOUNT else 0 end),0) as total_transactions,              
   IFNULL(ROUND( ((sum(case when STATUS in('paid','Success','processing','SUCCESS') and ModOfPayment  in('Settlement') then TOTAL_AMOUNT else 0 end) * 18 )/100),2),0) totaltax,              
     /* IFNULL( ROUND((sum(case when STATUS in('paid','Success','processing','SUCCESS') and ModOfPayment not in('Settlement','Refund','Chargeback','Hold') then TOTAL_AMOUNT else 0 end) * 100/   sum(case when ModOfPayment not in('Settlement','Refund','Charge
  
back','Hold') then TOTAL_AMOUNT else 0 end)),2),0) as success_percentage   */         
'0' as success_percentage
  from  MONEY_TRANSFER_PAYIN where  ModOfPayment in('collection','Settlement')              
  and CREATED_BY =v_CREATED_BY           
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate              
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate;              
    
              
              
select              
  IFNULL(sum(case when STATUS in('Failed','FAILED','failed') then 1 else 0 end),0) as failed,              
 IFNULL( sum(case when  STATUS in('paid','Success','processing','SUCCESS')  then 1 else 0 end),0) as success,              
   IFNULL( sum(case when  STATUS is NULL  then 1 else 0 end),0) as pending,            
 IFNULL(  sum(case when  STATUS not in('paid','Success','processing','SUCCESS','Failed','FAILED','failed')  then 1 else 0 end),0) as Cancelled,              
 IFNULL( count(TRANSACTIONID),0) as total_transactions from  MONEY_TRANSFER_PAYIN where  ModOfPayment in('collection')              
  and CREATED_BY =v_CREATED_BY              
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate              
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate;              
              
                
select              
 IFNULL( sum(case when STATUS in('Failed','FAILED','failed') then 1 else 0 end),0) as payout_failed,              
  IFNULL(sum(case when  STATUS in('paid','Success','SUCCESS')  then 1 else 0 end),0) as payout_success,              
   IFNULL(sum(case when  STATUS not in('Failed','FAILED','paid','Success','processing','SUCCESS','failed')  then 1 else 0 end),0) as payout_pending ,              
 IFNULL( count(TRANSACTIONID),0) as payout_total_transactions from  MONEY_TRANSFER_PAYOUT where CREATED_BY=v_CREATED_BY               
   and ModOfPayment not in('Settlement','collection','Refund','Chargeback','Hold')              
 #and STATUS='Success'              
 #AND CREATED_ON>=v_fromdate              
 #AND CREATED_ON<=v_todate               
and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate              
 and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate;
  ELSE
  select              
  IFNULL(sum(case when STATUS in('paid','Success','processing','SUCCESS') then TAX else 0 end),0) as total_fees,              
  IFNULL( sum(case when ModOfPayment not in('Settlement','Refund','Chargeback','Hold') then TOTAL_AMOUNT else 0 end),0) as total_transactions,              
   IFNULL(ROUND( ((sum(case when STATUS in('paid','Success','processing','SUCCESS') and ModOfPayment  in('Settlement') then TOTAL_AMOUNT else 0 end) * 18 )/100),2),0) totaltax,              
      #IFNULL( ROUND((sum(case when STATUS in('paid','Success','processing','SUCCESS') and ModOfPayment not in('Settlement','Refund','Chargeback','Hold') then TOTAL_AMOUNT else 0 end) * 100/   sum(case when ModOfPayment not in('Settlement','Refund','Charge  
#back','Hold') then TOTAL_AMOUNT else 0 end)),2),0)  
0 as success_percentage              
  from  MONEY_TRANSFER_PAYIN where  ModOfPayment in('collection','Settlement')                      
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate              
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate;
  END IF;              
              
              
              
select              
  IFNULL(sum(case when STATUS in('Failed','FAILED','failed') then 1 else 0 end),0) as failed,              
 IFNULL( sum(case when  STATUS in('paid','Success','processing','SUCCESS')  then 1 else 0 end),0) as success,              
   IFNULL( sum(case when  STATUS is NULL  then 1 else 0 end),0) as pending,            
 IFNULL(  sum(case when  STATUS not in('paid','Success','processing','SUCCESS','Failed','FAILED','failed')  then 1 else 0 end),0) as Cancelled,              
 IFNULL( count(TRANSACTIONID),0) as total_transactions from  MONEY_TRANSFER_PAYIN where  ModOfPayment in('collection')              
              
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate              
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate;              
              
                
select              
 IFNULL( sum(case when STATUS in('Failed','FAILED','failed') then 1 else 0 end),0) as payout_failed,              
  IFNULL(sum(case when  STATUS in('paid','Success','SUCCESS')  then 1 else 0 end),0) as payout_success,              
   IFNULL(sum(case when  STATUS not in('Failed','FAILED','paid','Success','processing','SUCCESS','failed')  then 1 else 0 end),0) as payout_pending ,              
 IFNULL( count(TRANSACTIONID),0) as payout_total_transactions from  MONEY_TRANSFER_PAYOUT where ModOfPayment not in('Settlement','collection','Refund','Chargeback','Hold')              
 #and STATUS='Success'              
 #AND CREATED_ON>=v_fromdate              
 #AND CREATED_ON<=v_todate               
and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate              
 and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate;         
              
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_GROUP_Payin_Revenue_PG`;
CREATE PROCEDURE `USP_GROUP_Payin_Revenue_PG`(
    IN v_CREATED_BY VARCHAR(10),
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10)
)
BEGIN
BEGIN                
                 
                
                
/*DECLARE v_total_volume numeric(10,2);                
DECLARE v_total_fees numeric(10,2);                
DECLARE v_total_tax numeric(10,2);                
DECLARE v_success_ratio numeric(10,2);                
DECLARE v_total_transactions int;                
DECLARE v_success int;                
DECLARE v_failed int;                
DECLARE v_Cancelled int;                
DECLARE v_payout_total_transactions int;                
DECLARE v_ayout_success int;                
DECLARE v_payout_failed int;                
DECLARE v_Cpayout_pending int;                
DECLARE v_successRatio int;                
                
set v_total_volume =(select sum(TOTAL_AMOUNT)                 
  from MONEY_TRANSFER_PAYIN where CREATED_BY=v_CREATED_BY and ModOfPayment in('collection')                
  and CREATED_BY =v_CREATED_BY                
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate                
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate                
 order by UPDATED_ON desc);                
 set v_successRatio  =(select sum(TRANSACTIONID)                 
  from MONEY_TRANSFER_PAYIN where CREATED_BY=v_CREATED_BY and ModOfPayment in('collection')                
  and CREATED_BY =v_CREATED_BY                
  and  STATUS in('paid','Success','processing','SUCCESS')                
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate                
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate                
 order by UPDATED_ON desc)                
set v_total_fees  =(select sum(TAX)                 
  from MONEY_TRANSFER_PAYIN where CREATED_BY=v_CREATED_BY and ModOfPayment in('collection')                
  #and  IFNULL(UTR,'0')<>'0'                
  and STATUS in('paid','Success','processing','SUCCESS')                 
   and CREATED_BY =v_CREATED_BY                
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate                
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate                
 order by UPDATED_ON desc);                
set v_total_tax  =(select sum(TAX)                 
  from MONEY_TRANSFER_PAYIN where CREATED_BY=v_CREATED_BY and ModOfPayment in('collection')                
  #and  IFNULL(UTR,'0')<>'0'                
  and STATUS in('paid','Success','processing','SUCCESS')                 
   and CREATED_BY =v_CREATED_BY                
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate                
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate                
 order by UPDATED_ON desc)                
set v_success_ratio  = (v_successRatio * 100) / v_total_volume ;                
                
                
*/         
        
IF ((v_CREATED_BY !=0)) THEN
  select                
  IFNULL(sum(case when STATUS in('paid','Success','processing','SUCCESS') then TAX else 0 end),0) as total_fees,                
  IFNULL( sum(case when ModOfPayment not in('Settlement','Refund','Chargeback','Hold') then TOTAL_AMOUNT else 0 end),0) as total_transactions,                
   IFNULL(ROUND( ((sum(case when STATUS in('paid','Success','processing','SUCCESS') and ModOfPayment  in('Settlement') then TOTAL_AMOUNT else 0 end) * 18 )/100),2),0) totaltax,                
     /* IFNULL( ROUND((sum(case when STATUS in('paid','Success','processing','SUCCESS') and ModOfPayment not in('Settlement','Refund','Chargeback','Hold') then TOTAL_AMOUNT else 0 end) * 100/   sum(case when ModOfPayment not in('Settlement','Refund','Char
ge  
    
back','Hold') then TOTAL_AMOUNT else 0 end)),2),0) as success_percentage   */           
'0' as success_percentage  
  from  MONEY_TRANSFER_PAYIN_PG where  ModOfPayment in('collection','Settlement')                
  and CREATED_BY =v_CREATED_BY             
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate                
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate;                
      
                
                
select                
  IFNULL(sum(case when STATUS in('Failed','FAILED','failed') then 1 else 0 end),0) as failed,                
 IFNULL( sum(case when  STATUS in('paid','Success','processing','SUCCESS')  then 1 else 0 end),0) as success,                
   IFNULL( sum(case when  STATUS is NULL  then 1 else 0 end),0) as pending,              
 IFNULL(  sum(case when  STATUS not in('paid','Success','processing','SUCCESS','Failed','FAILED','failed')  then 1 else 0 end),0) as Cancelled,                
 IFNULL( count(TRANSACTIONID),0) as total_transactions from  MONEY_TRANSFER_PAYIN_PG where  ModOfPayment in('collection')                
  and CREATED_BY =v_CREATED_BY                
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate                
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate;
  ELSE
  select                
  IFNULL(sum(case when STATUS in('paid','Success','processing','SUCCESS') then TAX else 0 end),0) as total_fees,                
  IFNULL( sum(case when ModOfPayment not in('Settlement','Refund','Chargeback','Hold') then TOTAL_AMOUNT else 0 end),0) as total_transactions,                
   IFNULL(ROUND( ((sum(case when STATUS in('paid','Success','processing','SUCCESS') and ModOfPayment  in('Settlement') then TOTAL_AMOUNT else 0 end) * 18 )/100),2),0) totaltax,                
      0 as success_percentage                
  from  MONEY_TRANSFER_PAYIN_PG where  ModOfPayment in('collection','Settlement')                        
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate                
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate;
  END IF;                
                
                
                
select                
  IFNULL(sum(case when STATUS in('Failed','FAILED','failed') then 1 else 0 end),0) as failed,                
 IFNULL( sum(case when  STATUS in('paid','Success','processing','SUCCESS')  then 1 else 0 end),0) as success,                
   IFNULL( sum(case when  STATUS is NULL  then 1 else 0 end),0) as pending,              
 IFNULL(  sum(case when  STATUS not in('paid','Success','processing','SUCCESS','Failed','FAILED','failed')  then 1 else 0 end),0) as Cancelled,                
 IFNULL( count(TRANSACTIONID),0) as total_transactions from  MONEY_TRANSFER_PAYIN_PG where  ModOfPayment in('collection')                
                
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate                
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate;                
                
                  

END;
END
$$

DROP PROCEDURE IF EXISTS `usp_Insert_ServiceRequestResponseLog`;
CREATE PROCEDURE `usp_Insert_ServiceRequestResponseLog`(
    IN v_MethodName VARCHAR(50),
    IN v_RequestLog VARCHAR(3000),
    IN v_ResponseLog VARCHAR(3000)
)
BEGIN
Begin 
 Insert into tMobileServiceRequestResponseLog (MethodName,RequestLog,ResponseLog,CreatedOn)
 Values (v_MethodName,v_RequestLog,v_ResponseLog,NOW())

 ;select cast(LAST_INSERT_ID() AS CHAR) TRANSACTIONID;
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_INSERT_SLABS`;
CREATE PROCEDURE `USP_INSERT_SLABS`(
    IN v_slabfrom INT,
    IN v_slabto INT,
    IN v_service VARCHAR(50),
    IN v_mode VARCHAR(50),
    IN v_charges DECIMAL(18,0)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
	INSERT INTO Slab_Master
           (slabfrom
           ,slabto
           ,service
           ,mode
           ,charges)
     VALUES
           (v_slabfrom,
			v_slabto ,
			v_service,
			v_mode ,
			v_charges
            );
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_MONEYTRANSFER_REVERSE_INSERT`;
CREATE PROCEDURE `USP_MONEYTRANSFER_REVERSE_INSERT`(
    IN v_AMOUNT VARCHAR(50),
    IN v_CREATED_BY VARCHAR(10),
    IN v_ModOfPayment VARCHAR(50),
    IN v_REASON LONGTEXT
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	DECLARE v_benid varchar(10) DEFAULT '0';
	IF (v_ModOfPayment='Collection') THEN
  UPDATE UserMaster
			SET Collection_Amount =Collection_Amount-cast(v_AMOUNT AS DECIMAL)
			 WHERE UserId=v_CREATED_BY;
  ELSE
  UPDATE UserMaster
			SET Available_Amount =Available_Amount-cast(v_AMOUNT AS DECIMAL)
			 WHERE UserId=v_CREATED_BY;
  END IF;
	INSERT INTO MONEY_TRANSFER
           (SERVICE_NAME
		   ,AMOUNT
           ,CREATED_ON
           ,CREATED_BY
           ,ModOfPayment
		   ,Tax 
		   ,Tax_AMOUNT
		   ,Total_AMOUNT
		   ,Total_CD_Amt
		   ,REASON
		   ,AccountNo
		   ,MobileNo
		   )
     VALUES('Reversal'
           ,v_AMOUNT
           ,NOW()
           ,v_CREATED_BY
           ,'charge back'
		   ,'0' 
		   ,'0'
		   ,v_AMOUNT
		   ,case when v_ModOfPayment='Collection' or v_ModOfPayment='Settlement' then (select IFNULL(Collection_Amount,v_AMOUNT) from UserMaster where UserID=v_CREATED_BY)
		   else (select Available_Amount from UserMaster where UserID=v_CREATED_BY) end
		   ,v_REASON
		   ,'0'
		   ,'0'
		   )
	;select cast(LAST_INSERT_ID() AS CHAR) TRANSACTIONID;
	
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_MONEYTRANSFER_TRANS_INSERT`;
CREATE PROCEDURE `USP_MONEYTRANSFER_TRANS_INSERT`(
    IN v_SERVICE_NAME VARCHAR(50),
    IN v_AMOUNT VARCHAR(50),
    IN v_AccountNo VARCHAR(30),
    IN v_MobileNo VARCHAR(20),
    IN v_CREATED_BY VARCHAR(10),
    IN v_ServiceVendor VARCHAR(100),
    IN v_ModOfPayment VARCHAR(50),
    IN v_Tax VARCHAR(10),
    IN v_Tax_AMOUNT VARCHAR(50),
    IN v_Total_AMOUNT VARCHAR(50),
    IN v_user_order_id VARCHAR(200),
    IN v_bankname VARCHAR(50),
    IN v_ifsc VARCHAR(50),
    IN v_holdername VARCHAR(100)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	DECLARE v_benid varchar(10) DEFAULT '0';
	
	INSERT INTO MONEY_TRANSFER
           (SERVICE_NAME
           ,AMOUNT
           ,AccountNo
           ,MobileNo
           ,BENEFICIAL_ID
           ,CREATED_ON
           ,CREATED_BY
           ,ServiceVendor
           ,ModOfPayment
		   ,Tax 
		   ,Tax_AMOUNT
		   ,Total_AMOUNT
		   ,user_order_id
		   ,BankName 
		   ,IFSC
		   ,HolderName
		   ,Total_CD_Amt
		   )
     VALUES(v_SERVICE_NAME
           ,v_AMOUNT
           ,v_AccountNo
           ,IFNULL(v_MobileNo,'999999999')
           ,(SELECT IFNULL(CardNo,'0') FROM AddBeneficiary WHERE AccountNo=v_AccountNo)
           ,NOW()
           ,v_CREATED_BY
           ,v_ServiceVendor
           ,v_ModOfPayment
		   ,v_Tax 
		   ,v_Tax_AMOUNT
		   ,v_Total_AMOUNT
		   ,v_user_order_id
		   ,v_bankname 
		   ,v_ifsc
		   ,v_holdername
		   ,case when v_ModOfPayment='Collection' or v_ModOfPayment='Settlement' then (select IFNULL(Collection_Amount,v_AMOUNT) from UserMaster where UserID=v_CREATED_BY)
		   else (select Available_Amount from UserMaster where UserID=v_CREATED_BY) end
		   )
	;select cast(LAST_INSERT_ID() AS CHAR) TRANSACTIONID LIMIT 1;
	
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_MONEYTRANSFER_TRANS_INSERT_PAYIN`;
CREATE PROCEDURE `USP_MONEYTRANSFER_TRANS_INSERT_PAYIN`(
    IN v_SERVICE_NAME VARCHAR(50),
    IN v_AMOUNT VARCHAR(50),
    IN v_AccountNo VARCHAR(30),
    IN v_MobileNo VARCHAR(20),
    IN v_CREATED_BY VARCHAR(10),
    IN v_ServiceVendor VARCHAR(100),
    IN v_ModOfPayment VARCHAR(50),
    IN v_Tax VARCHAR(10),
    IN v_Tax_AMOUNT VARCHAR(50),
    IN v_Total_AMOUNT VARCHAR(50),
    IN v_user_order_id VARCHAR(200),
    IN v_bankname VARCHAR(50),
    IN v_ifsc VARCHAR(50),
    IN v_holdername VARCHAR(100),
    IN v_TrFrom VARCHAR(5),
    IN v_TRSID VARCHAR(15),
    IN v_REQUEST_IP_ADDRESS VARCHAR(50)
)
BEGIN
BEGIN  
 # added to prevent extra result sets from  
 # interfering with SELECT statements.  
 DECLARE v_benid varchar(10) DEFAULT '0';  
   
 INSERT INTO MONEY_TRANSFER_PAYIN  
           (SERVICE_NAME  
           ,AMOUNT  
           ,AccountNo  
           ,MobileNo  
           ,BENEFICIAL_ID  
           ,CREATED_ON  
           ,CREATED_BY  
           ,ServiceVendor  
           ,ModOfPayment  
     ,Tax   
     ,Tax_AMOUNT  
     ,Total_AMOUNT  
     ,user_order_id  
     ,BankName   
     ,IFSC  
     ,HolderName  
     ,Total_CD_Amt  
     ,TrFrom  
     ,TRSID,  
     REQUEST_IP_ADDRESS,
	 STATUS,
	 UTR,
	 UPDATED_ON
     )  
     VALUES(v_SERVICE_NAME  
           ,v_AMOUNT  
           ,v_AccountNo  
           ,IFNULL(v_MobileNo,'999999999')  
           ,''  
           ,NOW()  
           ,v_CREATED_BY  
           ,v_ServiceVendor  
           ,v_ModOfPayment  
     ,v_Tax   
     ,v_Tax_AMOUNT  
     ,v_Total_AMOUNT  
     ,v_user_order_id  
     ,v_bankname   
     ,v_ifsc  
     ,v_holdername  
     ,case when v_ModOfPayment='Collection' or v_ModOfPayment='Settlement' then (select IFNULL(Collection_Amount,v_AMOUNT) from UserMaster where UserID=v_CREATED_BY)  
     else (select Available_Amount from UserMaster where UserID=v_CREATED_BY) end,  
      v_TrFrom,  
   v_TRSID,  
     v_REQUEST_IP_ADDRESS ,
	 'CREATED',
	 '0',
	 NOW()
     )  
 ;select cast(LAST_INSERT_ID() AS CHAR) TRANSACTIONID;  
   
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_MONEYTRANSFER_TRANS_INSERT_PAYIN_PG`;
CREATE PROCEDURE `USP_MONEYTRANSFER_TRANS_INSERT_PAYIN_PG`(
    IN v_SERVICE_NAME VARCHAR(50),
    IN v_AMOUNT VARCHAR(50),
    IN v_AccountNo VARCHAR(30),
    IN v_MobileNo VARCHAR(20),
    IN v_CREATED_BY VARCHAR(10),
    IN v_ServiceVendor VARCHAR(100),
    IN v_ModOfPayment VARCHAR(50),
    IN v_Tax VARCHAR(10),
    IN v_Tax_AMOUNT VARCHAR(50),
    IN v_Total_AMOUNT VARCHAR(50),
    IN v_user_order_id VARCHAR(200),
    IN v_bankname VARCHAR(50),
    IN v_ifsc VARCHAR(50),
    IN v_holdername VARCHAR(100),
    IN v_TrFrom VARCHAR(5),
    IN v_TRSID VARCHAR(15),
    IN v_REQUEST_IP_ADDRESS VARCHAR(50),
    IN v_Return_URL TEXT
)
BEGIN
BEGIN    
 # added to prevent extra result sets from    
 # interfering with SELECT statements.    
 DECLARE v_benid varchar(10) DEFAULT '0';    
     
 INSERT INTO MONEY_TRANSFER_PAYIN_PG    
           (SERVICE_NAME    
           ,AMOUNT    
           ,AccountNo    
           ,MobileNo    
           ,BENEFICIAL_ID    
           ,CREATED_ON    
           ,CREATED_BY    
           ,ServiceVendor    
           ,ModOfPayment    
     ,Tax     
     ,Tax_AMOUNT    
     ,Total_AMOUNT    
     ,user_order_id    
     ,BankName     
     ,IFSC    
     ,HolderName    
     ,Total_CD_Amt    
     ,TrFrom    
     ,TRSID,    
     REQUEST_IP_ADDRESS,  
  STATUS,  
  UTR,  
  UPDATED_ON  ,
  ReturnURL
     )    
     VALUES(v_SERVICE_NAME    
           ,v_AMOUNT    
           ,v_AccountNo    
           ,IFNULL(v_MobileNo,'999999999')    
           ,''    
           ,NOW()    
           ,v_CREATED_BY    
           ,v_ServiceVendor    
           ,v_ModOfPayment    
     ,v_Tax     
     ,v_Tax_AMOUNT    
     ,v_Total_AMOUNT    
     ,v_user_order_id    
     ,v_bankname     
     ,v_ifsc    
     ,v_holdername    
     ,case when v_ModOfPayment='Collection' or v_ModOfPayment='Settlement' then (select IFNULL(Collection_Amount,v_AMOUNT) from UserMaster where UserID=v_CREATED_BY)    
     else (select Available_Amount from UserMaster where UserID=v_CREATED_BY) end,    
      v_TrFrom,    
   v_TRSID,    
     v_REQUEST_IP_ADDRESS ,  
  'CREATED',  
  '0',  
  NOW()  ,
  v_Return_URL
     )    
 ;select cast(LAST_INSERT_ID() AS CHAR) TRANSACTIONID;    
     
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_MONEYTRANSFER_TRANS_INSERT_PAYOUT`;
CREATE PROCEDURE `USP_MONEYTRANSFER_TRANS_INSERT_PAYOUT`(
    IN v_SERVICE_NAME VARCHAR(50),
    IN v_AMOUNT VARCHAR(50),
    IN v_AccountNo VARCHAR(30),
    IN v_MobileNo VARCHAR(20),
    IN v_CREATED_BY VARCHAR(10),
    IN v_ServiceVendor VARCHAR(100),
    IN v_ModOfPayment VARCHAR(50),
    IN v_Tax VARCHAR(10),
    IN v_Tax_AMOUNT VARCHAR(50),
    IN v_Total_AMOUNT VARCHAR(50),
    IN v_user_order_id VARCHAR(200),
    IN v_bankname VARCHAR(50),
    IN v_ifsc VARCHAR(50),
    IN v_holdername VARCHAR(100),
    IN v_REQUEST_IP_ADDRESS VARCHAR(50),
    IN v_ActivePayout INT
)
BEGIN
BEGIN  
 # added to prevent extra result sets from  
 # interfering with SELECT statements.  
 DECLARE v_benid varchar(10) DEFAULT '0';  
   
 INSERT INTO MONEY_TRANSFER_PAYOUT  
           (SERVICE_NAME  
           ,AMOUNT  
           ,AccountNo  
           ,MobileNo  
           ,BENEFICIAL_ID  
           ,CREATED_ON  
           ,CREATED_BY  
           ,ServiceVendor  
           ,ModOfPayment  
     ,Tax   
     ,Tax_AMOUNT  
     ,Total_AMOUNT  
     ,user_order_id  
     ,BankName   
     ,IFSC  
     ,HolderName  
     ,Total_CD_Amt  ,
	 REQUEST_IP_ADDRESS,
	  ActivePayout
     )  
     VALUES(v_SERVICE_NAME  
           ,v_AMOUNT  
           ,v_AccountNo  
           ,IFNULL(v_MobileNo,'999999999')  
           ,''
           , NOW() 
           ,v_CREATED_BY  
           ,v_ServiceVendor  
           ,v_ModOfPayment  
     ,v_Tax   
     ,v_Tax_AMOUNT  
     ,v_Total_AMOUNT  
     ,v_user_order_id  
     ,v_bankname   
     ,v_ifsc  
     ,v_holdername  
     ,case when v_ModOfPayment='Collection' or v_ModOfPayment='Settlement' then (select IFNULL(Collection_Amount,v_AMOUNT) from UserMaster where UserID=v_CREATED_BY)  
     else (select Available_Amount from UserMaster where UserID=v_CREATED_BY) end  ,
	 v_REQUEST_IP_ADDRESS,
	  v_ActivePayout
     )  
 ;select cast(LAST_INSERT_ID() AS CHAR) TRANSACTIONID;  
   
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_MONEYTRANSFER_TRANS_UPDATE`;
CREATE PROCEDURE `USP_MONEYTRANSFER_TRANS_UPDATE`(
    IN v_REFERENCE_NUMBER VARCHAR(50),
    IN v_TRANSACTIONID VARCHAR(50),
    IN v_STATUS VARCHAR(30),
    IN v_Name VARCHAR(30),
    IN v_UPDATED_BY VARCHAR(10)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	DECLARE v_vcnt int;
DECLARE v_user_order_id varchar(200);
	IF (v_STATUS in('Refund','reversed','failed')) THEN
  set v_user_order_id=(select user_order_id  FROM MONEY_TRANSFER WHERE TRANSACTIONID=v_TRANSACTIONID);
	set v_vcnt=(select COUNT(1)  FROM MONEY_TRANSFER as MONEY_TRANSFER WHERE user_order_id=v_user_order_id);
	IF (v_vcnt=1) THEN
    INSERT INTO MONEY_TRANSFER
           (SERVICE_NAME
           ,AMOUNT
           ,AccountNo
           ,MobileNo
           ,CREATED_ON
           ,CREATED_BY
           ,ServiceVendor
           ,ModOfPayment
		   ,Tax 
		   ,Tax_AMOUNT
		   ,Total_AMOUNT
		   ,user_order_id
		   ,BankName 
		   ,IFSC
		   ,HolderName
		   ,STATUS
		   ,UPDATED_ON
		   ,Total_CD_Amt
		   )
     (select SERVICE_NAME
           ,AMOUNT
           ,AccountNo
           ,MobileNo
           ,CREATED_ON
           ,CREATED_BY
           ,ServiceVendor
           ,ModOfPayment
		   ,Tax 
		   ,Tax_AMOUNT
		   ,Total_AMOUNT
		   ,user_order_id
		   ,BankName 
		   ,IFSC
		   ,HolderName
		   ,'Refund'
		   ,DATE_ADD(CAST(NOW() AS DATETIME), INTERVAL 750 MINUTE)
		   ,(CAST(Total_CD_Amt AS DECIMAL)+ cast(Total_AMOUNT AS DECIMAL))
		   from MONEY_TRANSFER where TRANSACTIONID=v_TRANSACTIONID
		   );
		   UPDATE UserMaster
		 SET Available_Amount =Available_Amount+(select cast(Total_AMOUNT AS DECIMAL) from MONEY_TRANSFER WHERE TRANSACTIONID=v_TRANSACTIONID)
		WHERE UserId=(select CREATED_BY from MONEY_TRANSFER WHERE TRANSACTIONID=v_TRANSACTIONID) ;
		UPDATE MONEY_TRANSFER SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS='failed',UPDATED_BY=v_UPDATED_BY,UPDATED_ON=DATE_ADD(CAST(NOW() AS DATETIME), INTERVAL 750 MINUTE) 
		WHERE TRANSACTIONID=v_TRANSACTIONID;
    END IF;
  ELSE
  UPDATE MONEY_TRANSFER SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS='Success',UPDATED_BY=v_UPDATED_BY,UPDATED_ON=DATE_ADD(CAST(NOW() AS DATETIME), INTERVAL 750 MINUTE)
		WHERE TRANSACTIONID=v_TRANSACTIONID;
  END IF;
	  
	SELECT CAST(user_order_id AS CHAR) TRANSACTIONID
      ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER
      ,SERVICE_NAME
	  ,ModOfPayment
      ,AMOUNT
      ,TAX
      ,TAX_AMOUNT
      ,TOTAL_AMOUNT
      ,AccountNo
      ,MobileNo
	  #,'Success'  STATUS
	 , case when CREATED_BY in('10021','10022','10023','10024','10025','10026','10027','10029','10030','10031','10032','10033','10034','10035'
			,'10036','10037','10038','10039','10040','10041','10042','10043','10044','10045','10046','11047','11048','11049','11050','11051','11052','11053','11054','11055','11056','11057'
			,'11058','11059','11060','11061','11062','11063','11064','11065','11066','11067','11068','11069','11070','11071','11072','11073','11074','11075','11076','11077','11078','11079'
			)then status else 'Success' end STATUS
      ,CREATED_ON
      ,CREATED_BY
      ,(SELECT BeneName FROM AddBeneficiary WHERE CardNo=MONEY_TRANSFER_RES.BENEFICIAL_ID) Name
      #,UTR
       FROM MONEY_TRANSFER as MONEY_TRANSFER_RES
	WHERE TRANSACTIONID=v_TRANSACTIONID;
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_MONEYTRANSFER_TRANS_UPDATE_PAYIN`;
CREATE PROCEDURE `USP_MONEYTRANSFER_TRANS_UPDATE_PAYIN`(
    IN v_REFERENCE_NUMBER VARCHAR(50),
    IN v_TRANSACTIONID VARCHAR(50),
    IN v_UTR VARCHAR(30),
    IN v_Amount DECIMAL(18, 2),
    IN v_STATUS VARCHAR(30)
)
BEGIN
BEGIN        
 # added to prevent extra result sets from        
 # interfering with SELECT statements.        
 DECLARE v_vcnt int;
DECLARE v_user_order_id varchar(200);        
 IF (v_STATUS in('Refund','reversed','failed','FAILED')) THEN
  set v_user_order_id=(select user_order_id  FROM MONEY_TRANSFER_PAYIN WHERE TRANSACTIONID=v_TRANSACTIONID);        
 set v_vcnt=(select COUNT(1)  FROM MONEY_TRANSFER_PAYIN as MONEY_TRANSFER WHERE user_order_id=v_user_order_id);        
 IF (v_vcnt=1) THEN
    INSERT INTO MONEY_TRANSFER_PAYIN        
           (SERVICE_NAME        
           ,AMOUNT        
           ,AccountNo        
           ,MobileNo        
           ,CREATED_ON        
           ,CREATED_BY        
           ,ServiceVendor        
           ,ModOfPayment        
     ,Tax         
     ,Tax_AMOUNT        
     ,Total_AMOUNT        
     ,user_order_id        
     ,BankName         
     ,IFSC        
     ,HolderName        
     ,STATUS        
     ,UPDATED_ON        
     ,Total_CD_Amt        
     )        
     (select SERVICE_NAME        
           ,AMOUNT        
           ,AccountNo        
           ,MobileNo        
           ,CREATED_ON        
           ,CREATED_BY        
           ,ServiceVendor        
           ,ModOfPayment        
     ,Tax         
     ,Tax_AMOUNT        
     ,Total_AMOUNT        
     ,user_order_id        
     ,BankName         
     ,IFSC        
     ,HolderName        
     ,'Refund'        
     ,NOW()      
     ,(CAST(Total_CD_Amt AS DECIMAL)+ cast(Total_AMOUNT AS DECIMAL))        
     from MONEY_TRANSFER_PAYIN where TRANSACTIONID=v_TRANSACTIONID        
     );        
     UPDATE UserMaster        
   SET Available_Amount =Available_Amount+(select cast(Total_AMOUNT AS DECIMAL) from MONEY_TRANSFER_PAYIN WHERE TRANSACTIONID=v_TRANSACTIONID)        
  WHERE UserId=(select CREATED_BY from MONEY_TRANSFER WHERE TRANSACTIONID=v_TRANSACTIONID) ;        
  UPDATE MONEY_TRANSFER_PAYIN SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS='FAILED',UPDATED_BY=1,UPDATED_ON=NOW()      
  WHERE TRANSACTIONID=v_TRANSACTIONID;
    END IF;
  ELSE
  UPDATE MONEY_TRANSFER_PAYIN SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,UTR=v_UTR,STATUS=v_STATUS,UPDATED_BY=1,UPDATED_ON=NOW()       
  WHERE TRANSACTIONID=v_TRANSACTIONID;
  END IF;        
           
 SELECT CAST(user_order_id AS CHAR) TRANSACTIONID        
      ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER        
      ,SERVICE_NAME        
   ,ModOfPayment        
      ,AMOUNT        
      ,TAX        
      ,TAX_AMOUNT        
      ,TOTAL_AMOUNT        
      ,AccountNo        
      ,MobileNo        
   #,'Success'  STATUS        
  , case when CREATED_BY in('10021','10022','10023','10024','10025','10026','10027','10029','10030','10031','10032','10033','10034','10035'        
   ,'10036','10037','10038','10039','10040','10041','10042','10043','10044','10045','10046','11047','11048','11049','11050','11051','11052','11053','11054','11055','11056','11057'        
   ,'11058','11059','11060','11061','11062','11063','11064','11065','11066','11067','11068','11069','11070','11071','11072','11073','11074','11075','11076','11077','11078','11079'        
   )then status else 'Success' end STATUS        
      ,CREATED_ON        
      ,CREATED_BY        
      ,'' Name        
      #,UTR        
       FROM MONEY_TRANSFER_PAYIN as MONEY_TRANSFER_RES        
 WHERE TRANSACTIONID=v_TRANSACTIONID;        
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_MONEYTRANSFER_TRANS_UPDATE_PAYIN_CCAVENUE`;
CREATE PROCEDURE `USP_MONEYTRANSFER_TRANS_UPDATE_PAYIN_CCAVENUE`(
    IN v_REFERENCE_NUMBER VARCHAR(50),
    IN v_TRANSACTIONID VARCHAR(50),
    IN v_UTR VARCHAR(30),
    IN v_Amount DECIMAL(18, 2),
    IN v_STATUS VARCHAR(30),
    IN v_ProtalName VARCHAR(100),
    IN v_ModeOFPayment VARCHAR(100)
)
BEGIN
BEGIN          
 # added to prevent extra result sets from          
 # interfering with SELECT statements.          
 DECLARE v_vcnt int;
DECLARE v_user_order_id varchar(200);          
 IF (v_STATUS in('Refund','reversed','failed','FAILED')) THEN
  set v_user_order_id=(select user_order_id  FROM MONEY_TRANSFER_PAYIN WHERE TRANSACTIONID=v_TRANSACTIONID);          
 set v_vcnt=(select COUNT(1)  FROM MONEY_TRANSFER_PAYIN as MONEY_TRANSFER WHERE user_order_id=v_user_order_id);          
 IF (v_vcnt=1) THEN
    INSERT INTO MONEY_TRANSFER_PAYIN          
           (SERVICE_NAME          
           ,AMOUNT          
           ,AccountNo          
           ,MobileNo          
           ,CREATED_ON          
           ,CREATED_BY          
           ,ServiceVendor          
           ,ModOfPayment          
     ,Tax           
     ,Tax_AMOUNT          
     ,Total_AMOUNT          
     ,user_order_id          
     ,BankName           
     ,IFSC          
     ,HolderName          
     ,STATUS          
     ,UPDATED_ON          
     ,Total_CD_Amt          
     )          
     (select SERVICE_NAME          
           ,AMOUNT          
           ,AccountNo          
           ,MobileNo          
           ,CREATED_ON          
           ,CREATED_BY          
           ,ServiceVendor          
           ,ModOfPayment          
     ,Tax           
     ,Tax_AMOUNT          
     ,Total_AMOUNT          
     ,user_order_id          
     ,BankName           
     ,IFSC          
     ,HolderName          
     ,'Refund'          
     ,NOW()        
     ,(CAST(Total_CD_Amt AS DECIMAL)+ cast(Total_AMOUNT AS DECIMAL))          
     from MONEY_TRANSFER_PAYIN where TRANSACTIONID=v_TRANSACTIONID          
     );          
     UPDATE UserMaster          
   SET Available_Amount =Available_Amount+(select cast(Total_AMOUNT AS DECIMAL) from MONEY_TRANSFER_PAYIN WHERE TRANSACTIONID=v_TRANSACTIONID)          
  WHERE UserId=(select CREATED_BY from MONEY_TRANSFER WHERE TRANSACTIONID=v_TRANSACTIONID) ;          
  UPDATE MONEY_TRANSFER_PAYIN SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS='FAILED',UPDATED_BY=1,UPDATED_ON=NOW(),PortalName=v_ProtalName,REMARKS=v_ModeOFPayment        
  WHERE TRANSACTIONID=v_TRANSACTIONID;
    END IF;
  ELSE
  UPDATE MONEY_TRANSFER_PAYIN SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,UTR=v_UTR,STATUS=v_STATUS,UPDATED_BY=1,UPDATED_ON=NOW(),PortalName=v_ProtalName,REMARKS=v_ModeOFPayment         
  WHERE TRANSACTIONID=v_TRANSACTIONID;
  END IF;          
             
 SELECT CAST(user_order_id AS CHAR) TRANSACTIONID          
      ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER          
      ,SERVICE_NAME          
   ,ModOfPayment          
      ,AMOUNT          
      ,TAX          
      ,TAX_AMOUNT          
      ,TOTAL_AMOUNT          
      ,AccountNo          
      ,MobileNo          
   #,'Success'  STATUS          
  , case when CREATED_BY in('10021','10022','10023','10024','10025','10026','10027','10029','10030','10031','10032','10033','10034','10035'          
   ,'10036','10037','10038','10039','10040','10041','10042','10043','10044','10045','10046','11047','11048','11049','11050','11051','11052','11053','11054','11055','11056','11057'          
   ,'11058','11059','11060','11061','11062','11063','11064','11065','11066','11067','11068','11069','11070','11071','11072','11073','11074','11075','11076','11077','11078','11079'          
   )then status else 'Success' end STATUS          
      ,CREATED_ON          
      ,CREATED_BY          
      ,'' Name          
      #,UTR          
       FROM MONEY_TRANSFER_PAYIN as MONEY_TRANSFER_RES          
 WHERE TRANSACTIONID=v_TRANSACTIONID;          
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_MONEYTRANSFER_TRANS_UPDATE_PAYIN_FAILED`;
CREATE PROCEDURE `USP_MONEYTRANSFER_TRANS_UPDATE_PAYIN_FAILED`(
    IN v_REFERENCE_NUMBER VARCHAR(50),
    IN v_TRANSACTIONID VARCHAR(50),
    IN v_UTR VARCHAR(30),
    IN v_Amount DECIMAL(18, 2),
    IN v_STATUS VARCHAR(30)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    UPDATE MONEY_TRANSFER_PAYIN SET REFERENCE_NUMBER=0,STATUS='FAILED',UPDATED_BY=1,UPDATED_ON=NOW()        
  WHERE TRANSACTIONID=v_TRANSACTIONID;  

       SELECT CAST(user_order_id AS CHAR) TRANSACTIONID        
      ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER        
      ,SERVICE_NAME        
   ,ModOfPayment        
      ,AMOUNT        
      ,TAX        
      ,TAX_AMOUNT        
      ,TOTAL_AMOUNT        
      ,AccountNo        
      ,MobileNo        
   ,status  STATUS               
      ,CREATED_ON        
      ,CREATED_BY        
      ,'' Name        
      #,UTR        
       FROM MONEY_TRANSFER_PAYIN as MONEY_TRANSFER_RES        
 WHERE TRANSACTIONID=v_TRANSACTIONID ;

END;
END
$$

DROP PROCEDURE IF EXISTS `USP_MONEYTRANSFER_TRANS_UPDATE_PAYIN_FAILED1`;
CREATE PROCEDURE `USP_MONEYTRANSFER_TRANS_UPDATE_PAYIN_FAILED1`(
    IN v_REFERENCE_NUMBER VARCHAR(50),
    IN v_TRANSACTIONID VARCHAR(50),
    IN v_UTR VARCHAR(30),
    IN v_Amount DECIMAL(18, 2),
    IN v_STATUS VARCHAR(30)
)
BEGIN
BEGIN          
       
         
 UPDATE MONEY_TRANSFER_PAYIN SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,UTR=v_UTR,STATUS=v_STATUS,UPDATED_BY=1,UPDATED_ON=NOW()         
  WHERE TRANSACTIONID=v_TRANSACTIONID          
          
             
 ;SELECT CAST(user_order_id AS CHAR) TRANSACTIONID          
      ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER          
      ,SERVICE_NAME          
   ,ModOfPayment          
      ,AMOUNT          
      ,TAX          
      ,TAX_AMOUNT          
      ,TOTAL_AMOUNT          
      ,AccountNo          
      ,MobileNo          
	  STATUS                    
      ,CREATED_ON          
      ,CREATED_BY          
      ,'' Name          
      #,UTR          
       FROM MONEY_TRANSFER_PAYIN as MONEY_TRANSFER_RES          
 WHERE TRANSACTIONID=v_TRANSACTIONID;          
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_MONEYTRANSFER_TRANS_UPDATE_PAYIN_FAILED1_PG`;
CREATE PROCEDURE `USP_MONEYTRANSFER_TRANS_UPDATE_PAYIN_FAILED1_PG`(
    IN v_REFERENCE_NUMBER VARCHAR(50),
    IN v_TRANSACTIONID VARCHAR(50),
    IN v_UTR VARCHAR(30),
    IN v_Amount DECIMAL(18, 2),
    IN v_STATUS VARCHAR(30)
)
BEGIN
BEGIN            
         
           
 UPDATE MONEY_TRANSFER_PAYIN_PG SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,UTR=v_UTR,STATUS=v_STATUS,UPDATED_BY=1,UPDATED_ON=NOW()           
  WHERE TRANSACTIONID=v_TRANSACTIONID            
            
               
 ;SELECT CAST(user_order_id AS CHAR) TRANSACTIONID            
      ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER            
      ,SERVICE_NAME            
   ,ModOfPayment            
      ,AMOUNT            
      ,TAX            
      ,TAX_AMOUNT            
      ,TOTAL_AMOUNT            
      ,AccountNo            
      ,MobileNo            
   STATUS                      
      ,CREATED_ON            
      ,CREATED_BY            
      ,'' Name            
      #,UTR            
       FROM MONEY_TRANSFER_PAYIN_PG as MONEY_TRANSFER_RES            
 WHERE TRANSACTIONID=v_TRANSACTIONID;            
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_MONEYTRANSFER_TRANS_UPDATE_PAYIN_PG`;
CREATE PROCEDURE `USP_MONEYTRANSFER_TRANS_UPDATE_PAYIN_PG`(
    IN v_REFERENCE_NUMBER VARCHAR(50),
    IN v_TRANSACTIONID VARCHAR(50),
    IN v_UTR VARCHAR(30),
    IN v_Amount DECIMAL(18, 2),
    IN v_STATUS VARCHAR(30),
    IN v_ProtalName VARCHAR(100),
    IN v_ModeOFPayment VARCHAR(100)
)
BEGIN
BEGIN                
 # added to prevent extra result sets from                
 # interfering with SELECT statements.                
 DECLARE v_vcnt int;
DECLARE v_user_order_id varchar(200);                
 IF (v_STATUS in('Refund','reversed','failed','FAILED')) THEN
  set v_user_order_id=(select user_order_id  FROM MONEY_TRANSFER_PAYIN_PG WHERE TRANSACTIONID=v_TRANSACTIONID);                
 set v_vcnt=(select COUNT(1)  FROM MONEY_TRANSFER_PAYIN_PG as MONEY_TRANSFER WHERE user_order_id=v_user_order_id);                
 IF (v_vcnt=1) THEN
    INSERT INTO MONEY_TRANSFER_PAYIN_PG                
           (SERVICE_NAME                
           ,AMOUNT                
           ,AccountNo                
           ,MobileNo                
           ,CREATED_ON                
           ,CREATED_BY                
           ,ServiceVendor                
           ,ModOfPayment                
     ,Tax                 
     ,Tax_AMOUNT                
     ,Total_AMOUNT                
     ,user_order_id                
     ,BankName                 
     ,IFSC                
     ,HolderName                
     ,STATUS                
     ,UPDATED_ON                
     ,Total_CD_Amt                
     )                
     (select SERVICE_NAME                
           ,AMOUNT                
           ,AccountNo                
           ,MobileNo                
           ,CREATED_ON                
           ,CREATED_BY                
           ,ServiceVendor                
           ,ModOfPayment                
     ,Tax                 
     ,Tax_AMOUNT                
     ,Total_AMOUNT                
     ,user_order_id                
     ,BankName                 
     ,IFSC                
     ,HolderName                
     ,'Refund'                
     ,NOW()              
     ,(CAST(Total_CD_Amt AS DECIMAL)+ cast(Total_AMOUNT AS DECIMAL))                
     from MONEY_TRANSFER_PAYIN_PG where TRANSACTIONID=v_TRANSACTIONID                
     );                
     UPDATE UserMaster                
   SET Available_Amount =Available_Amount+(select cast(Total_AMOUNT AS DECIMAL) from MONEY_TRANSFER_PAYIN WHERE TRANSACTIONID=v_TRANSACTIONID)                
  WHERE UserId=(select CREATED_BY from MONEY_TRANSFER_PAYIN_PG WHERE TRANSACTIONID=v_TRANSACTIONID) ;                
  UPDATE MONEY_TRANSFER_PAYIN_PG SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS='FAILED',UPDATED_BY=1,UPDATED_ON=NOW(),PortalName=v_ProtalName,REMARKS=v_ModeOFPayment              
  WHERE TRANSACTIONID=v_TRANSACTIONID;
    END IF;
  ELSE
  UPDATE MONEY_TRANSFER_PAYIN_PG SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,UTR=v_UTR,STATUS=v_STATUS,UPDATED_BY=1,UPDATED_ON=NOW(),PortalName=v_ProtalName,REMARKS=v_ModeOFPayment               
  WHERE TRANSACTIONID=v_TRANSACTIONID;
  END IF;                
                   
 SELECT CAST(user_order_id AS CHAR) TRANSACTIONID                
      ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER                
      ,SERVICE_NAME                
   ,ModOfPayment                
      ,AMOUNT                
      ,TAX                
      ,TAX_AMOUNT                
      ,TOTAL_AMOUNT                
      ,AccountNo                
      ,MobileNo              
     ,STATUS              
      ,CREATED_ON                
      ,CREATED_BY                
      ,'' Name                
      #,UTR                
       FROM MONEY_TRANSFER_PAYIN_PG as MONEY_TRANSFER_RES                
 WHERE TRANSACTIONID=v_TRANSACTIONID;                
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_MONEYTRANSFER_TRANS_UPDATE_PAYOUT`;
CREATE PROCEDURE `USP_MONEYTRANSFER_TRANS_UPDATE_PAYOUT`(
    IN v_REFERENCE_NUMBER VARCHAR(50),
    IN v_TRANSACTIONID VARCHAR(50),
    IN v_STATUS VARCHAR(30),
    IN v_Name VARCHAR(30),
    IN v_UPDATED_BY VARCHAR(10)
)
BEGIN
BEGIN       
 # added to prevent extra result sets from              
 # interfering with SELECT statements.              
 DECLARE v_vcnt int;
DECLARE v_user_order_id varchar(200);              
 IF (v_STATUS in('Refund','reversed','failed','FAILED')) THEN
  set v_user_order_id=(select user_order_id  FROM MONEY_TRANSFER_PAYOUT WHERE TRANSACTIONID=v_TRANSACTIONID);  
 set v_vcnt=(select COUNT(1)  FROM MONEY_TRANSFER_PAYOUT as MONEY_TRANSFER_PAYOUT WHERE user_order_id=v_user_order_id);    
 IF (v_vcnt=1) THEN
    INSERT INTO MONEY_TRANSFER_PAYOUT              
           (SERVICE_NAME              
           ,AMOUNT              
           ,AccountNo              
           ,MobileNo              
           ,CREATED_ON              
           ,CREATED_BY              
           ,ServiceVendor              
           ,ModOfPayment              
     ,Tax               
     ,Tax_AMOUNT              
     ,Total_AMOUNT              
     ,user_order_id              
     ,BankName               
     ,IFSC              
     ,HolderName              
     ,STATUS              
     ,UPDATED_ON              
     ,Total_CD_Amt              
     )              
     (select SERVICE_NAME              
           ,AMOUNT              
           ,AccountNo              
           ,MobileNo              
           ,CREATED_ON              
           ,CREATED_BY              
           ,ServiceVendor              
           ,ModOfPayment              
     ,Tax               
     ,Tax_AMOUNT              
     ,Total_AMOUNT              
     ,user_order_id              
     ,BankName               
     ,IFSC              
     ,HolderName              
     ,'REFUND'              
     ,NOW()           
     ,(CAST(Total_CD_Amt AS DECIMAL)+ cast(Total_AMOUNT AS DECIMAL))              
     from MONEY_TRANSFER_PAYOUT where TRANSACTIONID=v_TRANSACTIONID              
     );              
     UPDATE UserMaster              
   SET Available_Amount =Available_Amount+(select cast(Total_AMOUNT AS DECIMAL) from MONEY_TRANSFER_PAYOUT WHERE TRANSACTIONID=v_TRANSACTIONID)              
  WHERE UserId=(select CREATED_BY from MONEY_TRANSFER_PAYOUT WHERE TRANSACTIONID=v_TRANSACTIONID) ;              
  UPDATE MONEY_TRANSFER_PAYOUT SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS='FAILED',UPDATED_BY=v_UPDATED_BY,UPDATED_ON=NOW()    
  WHERE TRANSACTIONID=v_TRANSACTIONID;
    END IF;
  ELSE
  UPDATE MONEY_TRANSFER_PAYOUT SET UTR=v_Name,REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS=v_STATUS,UPDATED_BY=v_UPDATED_BY,UPDATED_ON=NOW()               
  WHERE TRANSACTIONID=v_TRANSACTIONID;
  END IF;              
                 
 SELECT CAST(user_order_id AS CHAR) TRANSACTIONID              
      ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER              
      ,SERVICE_NAME              
   ,ModOfPayment              
      ,AMOUNT              
      ,TAX              
      ,TAX_AMOUNT              
      ,TOTAL_AMOUNT              
      ,AccountNo              
      ,MobileNo              
   #,'Success'  STATUS              
       , STATUS              
      ,CREATED_ON              
      ,CREATED_BY              
      ,HolderName as Name              
      #,UTR              
       FROM MONEY_TRANSFER_PAYOUT as MONEY_TRANSFER_RES              
 WHERE TRANSACTIONID=v_TRANSACTIONID;              
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_MONEYTRANSFER_TRANS_UPDATE_PAYOUT_1`;
CREATE PROCEDURE `USP_MONEYTRANSFER_TRANS_UPDATE_PAYOUT_1`(
    IN v_REFERENCE_NUMBER VARCHAR(50),
    IN v_TRANSACTIONID VARCHAR(50),
    IN v_STATUS VARCHAR(30),
    IN v_Name VARCHAR(30),
    IN v_UPDATED_BY VARCHAR(10)
)
BEGIN
BEGIN       
 # added to prevent extra result sets from              
 # interfering with SELECT statements.              
 DECLARE v_vcnt int;
DECLARE v_user_order_id varchar(200);              
 IF (v_STATUS in('Refund','reversed','failed','FAILED')) THEN
  set v_user_order_id=(select user_order_id  FROM MONEY_TRANSFER_PAYOUT WHERE TRANSACTIONID=v_TRANSACTIONID);  
 select v_user_order_id;
 set v_vcnt=(select COUNT(1)  FROM MONEY_TRANSFER_PAYOUT  WHERE user_order_id=v_user_order_id);   
 
 select v_vcnt;
 IF (v_vcnt=1) THEN
    INSERT INTO MONEY_TRANSFER_PAYOUT              
           (SERVICE_NAME              
           ,AMOUNT              
           ,AccountNo              
           ,MobileNo              
           ,CREATED_ON              
           ,CREATED_BY              
           ,ServiceVendor              
           ,ModOfPayment              
     ,Tax               
     ,Tax_AMOUNT              
     ,Total_AMOUNT              
     ,user_order_id              
     ,BankName               
     ,IFSC              
     ,HolderName              
     ,STATUS              
     ,UPDATED_ON              
     ,Total_CD_Amt              
     )              
     (select SERVICE_NAME              
           ,AMOUNT              
           ,AccountNo              
           ,MobileNo              
           ,CREATED_ON              
           ,CREATED_BY              
           ,ServiceVendor              
           ,ModOfPayment              
     ,Tax               
     ,Tax_AMOUNT              
     ,Total_AMOUNT              
     ,user_order_id              
     ,BankName               
     ,IFSC              
     ,HolderName              
     ,'Refund'              
     ,NOW()           
     ,(CAST(Total_CD_Amt AS DECIMAL)+ cast(Total_AMOUNT AS DECIMAL))              
     from MONEY_TRANSFER_PAYOUT where TRANSACTIONID=v_TRANSACTIONID              
     );              
     UPDATE UserMaster              
   SET Available_Amount =Available_Amount+(select cast(Total_AMOUNT AS DECIMAL) from MONEY_TRANSFER_PAYOUT WHERE TRANSACTIONID=v_TRANSACTIONID)              
  WHERE UserId=(select CREATED_BY from MONEY_TRANSFER_PAYOUT WHERE TRANSACTIONID=v_TRANSACTIONID) ;              
  UPDATE MONEY_TRANSFER_PAYOUT SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS='failed',UPDATED_BY=v_UPDATED_BY,UPDATED_ON=NOW()    
  WHERE TRANSACTIONID=v_TRANSACTIONID;
    END IF;
  ELSE
  UPDATE MONEY_TRANSFER_PAYOUT SET UTR=v_Name,REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS=v_STATUS,UPDATED_BY=v_UPDATED_BY,UPDATED_ON=NOW()               
  WHERE TRANSACTIONID=v_TRANSACTIONID;
  END IF;              
                 
 SELECT CAST(user_order_id AS CHAR) TRANSACTIONID              
      ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER              
      ,SERVICE_NAME              
   ,ModOfPayment              
      ,AMOUNT              
      ,TAX              
      ,TAX_AMOUNT              
      ,TOTAL_AMOUNT              
      ,AccountNo              
      ,MobileNo              
   #,'Success'  STATUS              
       , STATUS              
      ,CREATED_ON              
      ,CREATED_BY              
      ,HolderName as Name              
      #,UTR              
       FROM MONEY_TRANSFER_PAYOUT as MONEY_TRANSFER_RES              
 WHERE TRANSACTIONID=v_TRANSACTIONID;              
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_MONEYTRANSFER_TRANS_UPDATE_PAYOUT_KP`;
CREATE PROCEDURE `USP_MONEYTRANSFER_TRANS_UPDATE_PAYOUT_KP`(
    IN v_REFERENCE_NUMBER VARCHAR(50),
    IN v_TRANSACTIONID VARCHAR(50),
    IN v_STATUS VARCHAR(30),
    IN v_Name VARCHAR(30),
    IN v_UPDATED_BY VARCHAR(10)
)
BEGIN
BEGIN            
 # added to prevent extra result sets from            
 # interfering with SELECT statements.            
 DECLARE v_vcnt int;
DECLARE v_user_order_id varchar(200);            
 IF (v_STATUS in('Refund','reversed','failed')) THEN
  set v_user_order_id=(select user_order_id  FROM MONEY_TRANSFER_PAYOUT WHERE TRANSACTIONID=v_TRANSACTIONID);            
 set v_vcnt=(select COUNT(1)  FROM MONEY_TRANSFER_PAYOUT as MONEY_TRANSFER_PAYOUT WHERE user_order_id=v_user_order_id);            
 IF (v_vcnt=1) THEN
    INSERT INTO MONEY_TRANSFER_PAYOUT            
           (SERVICE_NAME            
           ,AMOUNT            
           ,AccountNo            
           ,MobileNo            
           ,CREATED_ON            
           ,CREATED_BY            
           ,ServiceVendor            
           ,ModOfPayment            
     ,Tax             
     ,Tax_AMOUNT            
     ,Total_AMOUNT            
     ,user_order_id            
     ,BankName             
     ,IFSC            
     ,HolderName            
     ,STATUS            
     ,UPDATED_ON            
     ,Total_CD_Amt            
     )            
     (select SERVICE_NAME            
           ,AMOUNT            
           ,AccountNo            
           ,MobileNo            
           ,CREATED_ON            
           ,CREATED_BY            
           ,ServiceVendor            
           ,ModOfPayment            
     ,Tax             
     ,Tax_AMOUNT            
     ,Total_AMOUNT            
     ,user_order_id            
     ,BankName             
     ,IFSC            
     ,HolderName            
     ,'Refund'            
     ,NOW()         
     ,(CAST(Total_CD_Amt AS DECIMAL)+ cast(Total_AMOUNT AS DECIMAL))            
     from MONEY_TRANSFER_PAYOUT where TRANSACTIONID=v_TRANSACTIONID            
     );            
               
  UPDATE MONEY_TRANSFER_PAYOUT SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS='FAILED',UPDATED_BY=v_UPDATED_BY,UPDATED_ON=NOW()  
  WHERE TRANSACTIONID=v_TRANSACTIONID;
    END IF;
  ELSE
  UPDATE MONEY_TRANSFER_PAYOUT SET UTR=v_Name,REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS=v_STATUS,UPDATED_BY=v_UPDATED_BY,UPDATED_ON=NOW()             
  WHERE TRANSACTIONID=v_TRANSACTIONID;
  END IF;            
               
 SELECT CAST(user_order_id AS CHAR) TRANSACTIONID            
      ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER            
      ,SERVICE_NAME            
   ,ModOfPayment            
      ,AMOUNT            
      ,TAX            
      ,TAX_AMOUNT            
      ,TOTAL_AMOUNT            
      ,AccountNo            
      ,MobileNo            
   #,'Success'  STATUS            
       , STATUS            
      ,CREATED_ON            
      ,CREATED_BY            
      ,HolderName as Name            
      #,UTR            
       FROM MONEY_TRANSFER_PAYOUT as MONEY_TRANSFER_RES            
 WHERE TRANSACTIONID=v_TRANSACTIONID;            
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_Payin_Collection_Report`;
CREATE PROCEDURE `USP_Payin_Collection_Report`(
    IN v_CREATED_BY VARCHAR(10),
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10)
)
BEGIN
BEGIN    
     
    
    
    
    
    
    
    
select    
  IFNULL(count(TRANSACTIONID),0) as total_transactions,    
   IFNULL( sum(case when  STATUS in('paid','Success','processing','SUCCESS')  then 1 else 0 end),0) as success,    
 /*IFNULL( ROUND((sum(case when STATUS in('paid','Success','processing','SUCCESS') then TOTAL_AMOUNT else 0 end)/   sum(case when ModOfPayment not in('Settlement','Refund','Chargeback') then TOTAL_AMOUNT else 0 end)),2),0) as success_percentage,*/
 0 as success_percentage,
  IFNULL( sum(case when  STATUS in('Refund')  then 1 else 0 end),0) as Refunded,    
     IFNULL(sum(case when  STATUS in('Chargeback')  then 1 else 0 end),0) as chargeBack,    
      IFNULL( sum(case when  ModOfPayment in('Settlement','Refund','Chargeback')  then TOTAL_AMOUNT else 0 end),0) as Settlement,    
   IFNULL(sum(case when  STATUS not in('Failed','processing','FAILED','paid','Success','processing','SUCCESS','failed')  then 1 else 0 end),0) as Cancelled,    
   IFNULL( sum(case when STATUS in('paid','Success','processing','SUCCESS') then TAX else 0 end),0) as total_fees    
 from  MONEY_TRANSFER_PAYIN where CREATED_BY=v_CREATED_BY and ModOfPayment in('collection','Settlement')    
   
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate    
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate;    
    
      
    
    
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_Payin_Collection_Report_PG`;
CREATE PROCEDURE `USP_Payin_Collection_Report_PG`(
    IN v_CREATED_BY VARCHAR(10),
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10)
)
BEGIN
BEGIN      
       
      
      
      
      
      
      
      
select      
  IFNULL(count(TRANSACTIONID),0) as total_transactions,      
   IFNULL( sum(case when  STATUS in('paid','Success','processing','SUCCESS')  then 1 else 0 end),0) as success,      
 /*IFNULL( ROUND((sum(case when STATUS in('paid','Success','processing','SUCCESS') then TOTAL_AMOUNT else 0 end)/   sum(case when ModOfPayment not in('Settlement','Refund','Chargeback') then TOTAL_AMOUNT else 0 end)),2),0) as success_percentage,*/  
 0 as success_percentage,  
  IFNULL( sum(case when  STATUS in('Refund')  then 1 else 0 end),0) as Refunded,      
     IFNULL(sum(case when  STATUS in('Chargeback')  then 1 else 0 end),0) as chargeBack,      
      IFNULL( sum(case when  ModOfPayment in('Settlement','Refund','Chargeback')  then TOTAL_AMOUNT else 0 end),0) as Settlement,      
   IFNULL(sum(case when  STATUS not in('Failed','processing','FAILED','paid','Success','processing','SUCCESS','failed')  then 1 else 0 end),0) as Cancelled,      
   IFNULL( sum(case when STATUS in('paid','Success','processing','SUCCESS') then TAX else 0 end),0) as total_fees      
 from  MONEY_TRANSFER_PAYIN_PG where CREATED_BY=v_CREATED_BY and ModOfPayment in('collection','Settlement')      
     
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate      
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate;      
      
        
      
      
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_PAYIN_STATUS`;
CREATE PROCEDURE `USP_PAYIN_STATUS`(
    IN v_TransID VARCHAR(100)
)
BEGIN
BEGIN          
 # added to prevent extra result sets from          
 # interfering with SELECT statements.          
           
          
 DECLARE v_cnt int;          
set v_cnt=(SELECT COUNT(1) FROM MONEY_TRANSFER_PAYIN where  user_order_id = v_TransID);          
IF (( v_cnt=1)) THEN
  # Insert statements for procedure here          
 SELECT IFNULL(um.SID,'0') SID,um.Payin_Chnl,mt.user_order_id,mt.TRSID as trsid,      
 mt.TrFrom as trfrom, mt.TRANSACTIONID,  IFNULL(mt.REFERENCE_NUMBER,0) as RefNum,mt.AMOUNT as AMOUNT,mt.STATUS as STATUS,mt.UTR as UTR,mt.REFERENCE_NUMBER as REFERENCE_NUMBER FROM UserMaster um join MONEY_TRANSFER_PAYIN mt        
 on um.UserId=mt.CREATED_BY WHERE    mt.user_order_id = v_TransID;
  ELSE
  SELECT IFNULL(um.SID,'0') SID,um.Payin_Chnl,mt.user_order_id,mt.TRSID as trsid,      
 mt.TrFrom as trfrom, mt.TRANSACTIONID,  IFNULL(mt.REFERENCE_NUMBER,0) as RefNum,mt.AMOUNT as AMOUNT,mt.STATUS as STATUS,mt.UTR as UTR,mt.REFERENCE_NUMBER as REFERENCE_NUMBER  FROM UserMaster um join MONEY_TRANSFER_PAYIN mt        
 on um.UserId=mt.CREATED_BY WHERE    mt.TRANSACTIONID = v_TransID;
  END IF;        
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_PAYIN_STATUS_PG`;
CREATE PROCEDURE `USP_PAYIN_STATUS_PG`(
    IN v_TransID VARCHAR(100)
)
BEGIN
BEGIN              
 # added to prevent extra result sets from              
 # interfering with SELECT statements.              
               
              
 DECLARE v_cnt int;              
set v_cnt=(SELECT COUNT(1) FROM MONEY_TRANSFER_PAYIN_PG where  user_order_id = v_TransID);              
IF (( v_cnt=1)) THEN
  # Insert statements for procedure here              
 SELECT IFNULL(um.SID,'0') SID,um.Payin_Chnl,mt.user_order_id,mt.TRSID as trsid,          
 mt.TrFrom as trfrom, mt.TRANSACTIONID,  IFNULL(mt.REFERENCE_NUMBER,0) as RefNum,mt.AMOUNT as AMOUNT  FROM UserMaster um join MONEY_TRANSFER_PAYIN_PG mt            
 on um.UserId=mt.CREATED_BY WHERE    mt.user_order_id = v_TransID;
  ELSE
  SELECT IFNULL(um.SID,'0') SID,um.Payin_Chnl,mt.user_order_id,mt.TRSID as trsid,          
 mt.TrFrom as trfrom, mt.TRANSACTIONID,  IFNULL(mt.REFERENCE_NUMBER,0) as RefNum,mt.AMOUNT as AMOUNT  FROM UserMaster um join MONEY_TRANSFER_PAYIN_PG mt            
 on um.UserId=mt.CREATED_BY WHERE    mt.TRANSACTIONID = v_TransID;
  END IF;            
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_PAYIN_TRANSACTION_STATUS`;
CREATE PROCEDURE `USP_PAYIN_TRANSACTION_STATUS`(
    IN v_TRANSACTIONID VARCHAR(500)
)
BEGIN
BEGIN    
 # added to prevent extra result sets from    
 # interfering with SELECT statements.    
     
  
  
            
 DECLARE v_cnt int;            
set v_cnt=(SELECT COUNT(1) FROM MONEY_TRANSFER_PAYIN where  user_order_id = v_TRANSACTIONID);            
IF (( v_cnt=1)) THEN
  # Insert statements for procedure here            
SELECT  `TRANSACTIONID`    
      ,`REFERENCE_NUMBER`    
      ,`SERVICE_NAME`    
      ,`AMOUNT`    
      ,`TAX`    
      ,`TAX_AMOUNT`    
      ,`TOTAL_AMOUNT`    
      ,`AccountNo`    
      ,`MobileNo`    
     ,(case when `Status` IS NULL or `Status` = ''
        then 'CREATED'
        else `Status`
   end )as `Status`  
      ,`CREATED_ON`    
      ,`CREATED_BY`    
   
	   ,(case when `UPDATED_ON` IS NULL or `UPDATED_ON` = ''
        then NOW()
        else `UPDATED_ON`
   end )as `UPDATED_ON` 
      ,`UPDATED_BY`    
      ,`ModOfPayment`    
      ,`user_order_id`    
      ,`BankName`    
      ,`IFSC`    
      ,`HolderName`    
      ,`Total_CD_Amt`    
	   ,(case when `UTR` IS NULL or `UTR` = ''
        then '0'
        else `UTR`
   end ) as `UTR`
      ,`REASON`    
      ,`TrFrom`    
      ,`TRSID`    
      ,`TRUpdated`    
  FROM `MONEY_TRANSFER_PAYIN` mt where   mt.user_order_id = v_TRANSACTIONID;
  ELSE
  SELECT  `TRANSACTIONID`    
      ,`REFERENCE_NUMBER`    
      ,`SERVICE_NAME`    
      ,`AMOUNT`    
      ,`TAX`    
      ,`TAX_AMOUNT`    
      ,`TOTAL_AMOUNT`    
      ,`AccountNo`    
      ,`MobileNo`    
     ,(case when `Status` IS NULL or `Status` = ''
        then 'CREATED'
        else `Status`
   end )as `Status`  
      ,`CREATED_ON`    
      ,`CREATED_BY`    
     ,(case when `UPDATED_ON` IS NULL or `UPDATED_ON` = ''
        then NOW()
        else `UPDATED_ON`
   end )as `UPDATED_ON`   
      ,`UPDATED_BY`    
      ,`ModOfPayment`    
      ,`user_order_id`    
      ,`BankName`    
      ,`IFSC`    
      ,`HolderName`    
      ,`Total_CD_Amt`     
	   ,(case when `UTR` IS NULL or `UTR` = ''
        then '0'
        else `UTR`
   end ) as `UTR`
      ,`REASON`    
      ,`TrFrom`    
      ,`TRSID`    
      ,`TRUpdated`    
  FROM `MONEY_TRANSFER_PAYIN` mt  where   mt.TRANSACTIONID = v_TRANSACTIONID;
  END IF;          
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_Payout_Collection`;
CREATE PROCEDURE `USP_Payout_Collection`(
    IN v_CREATED_BY VARCHAR(10),
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10)
)
BEGIN
BEGIN          
           
          
          
          
          
          
          
          
            
select          
  IFNULL(sum(case when STATUS in('paid','Success','SUCCESS')  then TOTAL_AMOUNT else 0 end),0) as payout_total_amount,          
  IFNULL(count(TRANSACTIONID),0) as payout_total_transactions,          
  IFNULL(sum(case when STATUS in('paid','Success','SUCCESS')  then TAX else 0 end),0) as total_fees,          
   0 as totaltax,          
        
   IFNULL(sum(case when STATUS in('Failed','FAILED','failed')  then 1 else 0 end),0) as payout_failed,          
   IFNULL(sum(case when  STATUS in('paid','Success','SUCCESS')  then 1 else 0 end),0) as payout_success          
             
   from  MONEY_TRANSFER_PAYOUT where CREATED_BY=v_CREATED_BY           
   and ModOfPayment  in('IMPS','NEFT','UPI')          
 #and STATUS='Success'          
 #AND CREATED_ON>=v_fromdate          
 #AND CREATED_ON<=v_todate           
and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate          
 and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate;          
           
          
          
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_Payout_Status_Data`;
CREATE PROCEDURE `USP_Payout_Status_Data`(
    IN v_txtTrans VARCHAR(100)
)
BEGIN
BEGIN          
DECLARE v_cnt int;          
set v_cnt=(SELECT COUNT(*) FROM MONEY_TRANSFER_PAYOUT where  user_order_id = v_txtTrans);          
IF (( v_cnt>=1)) THEN
  SELECT IFNULL(REFERENCE_NUMBER, '') as message,1 as  transactionID, TRANSACTIONID as TransID,    
 user_order_id as userOrderID,TAX ,TOTAL_AMOUNT ,AMOUNT,BankName    
 ,ModOfPayment as ModeofPayment,AccountNo , HolderName ,IFSC,MobileNo,CREATED_ON , ActivePayout  ,IFNULL(STATUS ,'') as  STATUS     
                    
  FROM MONEY_TRANSFER_PAYOUT where #user_order_id=v_user_orderid  AND          
  user_order_id=v_txtTrans order by UPDATED_ON desc LIMIT 1;
  ELSE
  SELECT IFNULL(REFERENCE_NUMBER, '') as message,1 as  transactionID, TRANSACTIONID as TransID,    
 user_order_id as userOrderID,TAX ,TOTAL_AMOUNT ,AMOUNT,BankName    
 ,ModOfPayment as ModeofPayment ,AccountNo , HolderName ,IFSC,MobileNo,CREATED_ON , ActivePayout,IFNULL(STATUS ,'') as  STATUS             
             
                    
  FROM MONEY_TRANSFER_PAYOUT where #user_order_id=v_user_orderid  AND          
TRANSACTIONID =v_txtTrans;
  END IF;          
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_PAYOUT_TRANS_UPDATE`;
CREATE PROCEDURE `USP_PAYOUT_TRANS_UPDATE`(
    IN v_REFERENCE_NUMBER VARCHAR(50),
    IN v_TRANSACTIONID VARCHAR(50),
    IN v_STATUS VARCHAR(30),
    IN v_UTR VARCHAR(30),
    IN v_REASON VARCHAR(1000)
)
BEGIN
BEGIN    
 # added to prevent extra result sets from    
 # interfering with SELECT statements.    
 DECLARE v_vcnt int;
DECLARE v_user_order_id varchar(200);
DECLARE v_create varchar(50);    
 IF (v_STATUS in('Refund','reversed','failed','cancelled','FAILED')) THEN
  set v_user_order_id=(select user_order_id  FROM MONEY_TRANSFER_PAYOUT WHERE TRANSACTIONID=v_TRANSACTIONID);    
 #set v_create=(select CREATED_BY  FROM MONEY_TRANSFER WHERE TRANSACTIONID=v_TRANSACTIONID);    
 set v_vcnt=(select COUNT(1)  FROM MONEY_TRANSFER_PAYOUT as MONEY_TRANSFER WHERE user_order_id=v_user_order_id);    
 IF (v_vcnt=1) THEN
    INSERT INTO MONEY_TRANSFER_PAYOUT    
           (SERVICE_NAME    
           ,AMOUNT    
           ,AccountNo    
           ,MobileNo    
           ,CREATED_ON    
           ,CREATED_BY    
           ,ServiceVendor    
           ,ModOfPayment    
     ,Tax     
     ,Tax_AMOUNT    
     ,Total_AMOUNT    
     ,user_order_id    
     ,BankName     
     ,IFSC    
     ,HolderName    
     ,STATUS    
     ,UPDATED_ON    
     ,Total_CD_Amt    
     ,UTR    
     ,REASON    
     )    
     (select SERVICE_NAME    
           ,AMOUNT    
           ,AccountNo    
           ,MobileNo    
           ,CREATED_ON    
           ,CREATED_BY    
           ,ServiceVendor    
           ,ModOfPayment    
     ,Tax     
     ,Tax_AMOUNT    
     ,Total_AMOUNT    
     ,user_order_id    
     ,BankName     
     ,IFSC    
     ,HolderName    
     ,'Refund'    
     ,NOW()    
     ,(CAST(Total_CD_Amt AS DECIMAL)+ cast(Total_AMOUNT AS DECIMAL))    
     ,v_UTR    
     ,v_REASON    
     from MONEY_TRANSFER_PAYOUT where TRANSACTIONID=v_TRANSACTIONID    
         
     );    
    UPDATE UserMaster    
   SET Available_Amount =Available_Amount+(select cast(Total_AMOUNT AS DECIMAL) from MONEY_TRANSFER_PAYOUT WHERE TRANSACTIONID=v_TRANSACTIONID)    
  WHERE UserId=(select CREATED_BY from MONEY_TRANSFER_PAYOUT WHERE TRANSACTIONID=v_TRANSACTIONID) ;    
  UPDATE MONEY_TRANSFER_PAYOUT SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,STATUS='FAILED',UPDATED_BY='1',UPDATED_ON=NOW() ,UTR=v_UTR    
  WHERE TRANSACTIONID=v_TRANSACTIONID;
    END IF;
  ELSE
  UPDATE MONEY_TRANSFER_PAYOUT SET REFERENCE_NUMBER=v_REFERENCE_NUMBER,UTR=v_UTR,STATUS=v_STATUS,UPDATED_BY='1',UPDATED_ON=NOW()     
  WHERE TRANSACTIONID=v_TRANSACTIONID;
  END IF;    
       
 SELECT CAST(user_order_id AS CHAR) TRANSACTIONID    
      ,CAST(TRANSACTIONID AS CHAR) as REFERENCE_NUMBER    
      ,SERVICE_NAME    
   ,ModOfPayment    
      ,AMOUNT    
      ,TAX    
      ,TAX_AMOUNT    
      ,TOTAL_AMOUNT    
      ,AccountNo    
      ,MobileNo    
  , case when CREATED_BY in('10021','10022','10023','10024','10025','10026','10027','10028','10029','10030','10031','10032','10033','10034','10035','10036','10037'    
        ,'10038','10039','10040') then 'Success' else 'Success' end STATUS    
      ,CREATED_ON    
      ,CREATED_BY    
      ,'' Name    
          
       FROM MONEY_TRANSFER_PAYOUT as MONEY_TRANSFER_RES    
 WHERE TRANSACTIONID=v_TRANSACTIONID;    
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_PAYOUT_TRANSACTION_STATUS`;
CREATE PROCEDURE `USP_PAYOUT_TRANSACTION_STATUS`(
    IN v_TRANSACTIONID VARCHAR(500)
)
BEGIN
BEGIN    
 # added to prevent extra result sets from    
 # interfering with SELECT statements.    
     
 DECLARE v_cnt int;            
set v_cnt=(SELECT COUNT(*) FROM `MONEY_TRANSFER_PAYOUT` where  user_order_id = v_TRANSACTIONID);            
IF (( v_cnt>=1)) THEN
  # Insert statements for procedure here            
SELECT `TRANSACTIONID`    
      ,`REFERENCE_NUMBER`    
      ,`SERVICE_NAME`    
      ,`AMOUNT`    
      ,`TAX`    
      ,`TAX_AMOUNT`    
      ,`TOTAL_AMOUNT`    
      ,`AccountNo`    
      ,`MobileNo`    
      ,`STATUS`    
      ,`CREATED_ON`    
      ,`CREATED_BY`    
      ,`UPDATED_ON`    
      ,`UPDATED_BY`    
      ,`ModOfPayment`    
      ,`user_order_id`    
      ,`BankName`    
      ,`IFSC`    
      ,`HolderName`    
      ,`Total_CD_Amt`    
      ,`UTR`    
      ,`REASON`    
  FROM `MONEY_TRANSFER_PAYOUT` mt where   mt.user_order_id = v_TRANSACTIONID  order by UPDATED_ON desc LIMIT 1;
  ELSE
  SELECT  `TRANSACTIONID`    
      ,`REFERENCE_NUMBER`    
      ,`SERVICE_NAME`    
      ,`AMOUNT`    
      ,`TAX`    
      ,`TAX_AMOUNT`    
      ,`TOTAL_AMOUNT`    
      ,`AccountNo`    
      ,`MobileNo`    
      ,`STATUS`    
      ,`CREATED_ON`    
      ,`CREATED_BY`    
      ,`UPDATED_ON`    
      ,`UPDATED_BY`    
      ,`ModOfPayment`    
      ,`user_order_id`    
      ,`BankName`    
      ,`IFSC`    
      ,`HolderName`    
      ,`Total_CD_Amt`    
      ,`UTR`    
      ,`REASON`    
  FROM `MONEY_TRANSFER_PAYOUT` mt  where   mt.TRANSACTIONID = v_TRANSACTIONID;
  END IF;          
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_PAYTAX_List`;
CREATE PROCEDURE `USP_PAYTAX_List`(
    IN v_UserID VARCHAR(100)
)
BEGIN
BEGIN    
 # added to prevent extra result sets from    
 # interfering with SELECT statements.    
     
    
    # Select statements for procedure here    
 SELECT cast(IFNULL(Pay_Tax,10) AS CHAR) rate ,(select IFNULL(payout_flag,0) FROM UserMaster where UserID=v_UserID) payout_flag,MinimumPayoutAmount,RupessCharge,MaximumPayoutAmount,FlatRate  FROM User_Tax_Mst where UserID=v_UserID;    
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_PAYTAX_List_1`;
CREATE PROCEDURE `USP_PAYTAX_List_1`(
    IN v_UserID VARCHAR(100),
    IN v_Amount DOUBLE
)
BEGIN
BEGIN        
 # added to prevent extra result sets from        
 # interfering with SELECT statements.        
    
   
 IF ((v_UserID=50083)) THEN
  IF ((v_Amount < 1000)) THEN
    SELECT '7.67' as rate ,(select IFNULL(payout_flag,0) FROM UserMaster where UserID=v_UserID) payout_flag,MinimumPayoutAmount,7.67  as RupessCharge,MaximumPayoutAmount,FlatRate  FROM User_Tax_Mst where UserID=v_UserID ;
    ELSE
    IF ((v_Amount >= 1000 and  v_Amount < 25000)) THEN
      SELECT '10.62' as rate ,(select IFNULL(payout_flag,0) FROM UserMaster where UserID=v_UserID) payout_flag,MinimumPayoutAmount,10.62 as RupessCharge,MaximumPayoutAmount,FlatRate  FROM User_Tax_Mst where UserID=v_UserID ;
      ELSE
      SELECT '16.52' as rate ,(select IFNULL(payout_flag,0) FROM UserMaster where UserID=v_UserID) payout_flag,MinimumPayoutAmount,16.52 as RupessCharge,MaximumPayoutAmount,FlatRate  FROM User_Tax_Mst where UserID=v_UserID;
      END IF;
    END IF;
  ELSE
  SELECT cast(IFNULL(Pay_Tax,10) AS CHAR) rate ,(select IFNULL(payout_flag,0) FROM UserMaster where UserID=v_UserID) payout_flag,MinimumPayoutAmount,RupessCharge,MaximumPayoutAmount,FlatRate  FROM User_Tax_Mst where UserID=v_UserID;
  END IF;       
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_REQUEST_AddAmount_LIST_ForAdmin`;
CREATE PROCEDURE `USP_REQUEST_AddAmount_LIST_ForAdmin`(
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	
	SELECT TRANSACTIONID
      ,AMOUNT 
      ,(case when STATUS<>'Pending' then '<span style="color:green">Success</span>' else '<span style="color:red">'+STATUS+'</span>' end ) STATUS
      ,CREATED_ON Requestdate
      ,(case when PortalName is null then 'Payout/transfer' else PortalName end ) servicename
      ,ModOfPayment
      ,user_order_id ReferenceId
	  ,CREATED_BY UserID
	  FROM MONEY_TRANSFER where DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate
	  and PortalName='Request For Add Amount'
	  order by TRANSACTIONID desc;
	
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_REQUEST_FOR_ADDAMOUNT`;
CREATE PROCEDURE `USP_REQUEST_FOR_ADDAMOUNT`(
    IN v_SERVICE_NAME VARCHAR(50),
    IN v_AMOUNT VARCHAR(50),
    IN v_CREATED_BY VARCHAR(10),
    IN v_Mode VARCHAR(10),
    IN v_Ref_No VARCHAR(50)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	DECLARE v_benid varchar(10) DEFAULT '0';
	
	INSERT INTO MONEY_TRANSFER
           (SERVICE_NAME
           ,AMOUNT
           ,MobileNo
		   ,TAX
		   ,TAX_AMOUNT
		   ,TOTAL_AMOUNT
		   ,AccountNo
           ,CREATED_ON
           ,CREATED_BY
           ,STATUS
		   ,PortalName
		   ,ModOfPayment
		   ,user_order_id
		   )
     VALUES(v_SERVICE_NAME
           ,v_AMOUNT
           ,(SELECT IFNULL(Mobile,999999999)  FROM UserMaster where USERID=v_CREATED_BY)
		   ,0
		   ,0
		   ,v_AMOUNT
		   ,0
          ,NOW()
           ,v_CREATED_BY
		   ,'Pending'
		   ,'Request For Add Amount'
		   ,v_Mode
		   ,v_Ref_No
	  	   );
	
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_REQUEST_SETTLEMENT_LIST`;
CREATE PROCEDURE `USP_REQUEST_SETTLEMENT_LIST`(
    IN v_CREATED_BY VARCHAR(10)
)
BEGIN
BEGIN  
 # added to prevent extra result sets from  
 # interfering with SELECT statements.  
   
 SELECT TRANSACTIONID  
      ,AMOUNT  
      , STATUS  
      ,CREATED_ON Requestdate  
      ,PortalName servicename  
      ,ModOfPayment  
      ,user_order_id ReferenceId  
   FROM MONEY_TRANSFER_PAYIN where CREATED_BY=v_CREATED_BY and ModOfPayment='Settlement'  
   order by TRANSACTIONID desc LIMIT 50;  
   
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_REQUEST_SETTLEMENT_LIST_ForAdmin`;
CREATE PROCEDURE `USP_REQUEST_SETTLEMENT_LIST_ForAdmin`(
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10)
)
BEGIN
BEGIN    
 # added to prevent extra result sets from    
 # interfering with SELECT statements.    
     
 SELECT TRANSACTIONID    
      ,AMOUNT     
   ,AccountNo    
   ,IFSC    
   ,HolderName    
      ,(case when STATUS in('Success','queued','processing') then '<span style="color:green">Success</span>' else '<span style="color:red">'+STATUS+'</span>' end ) STATUS    
      ,CREATED_ON Requestdate    
      ,(case when PortalName is null then 'Payout' else PortalName end ) servicename    
      ,'Settlement' as  ModOfPayment    
      ,UTR ReferenceId    
   ,CREATED_BY UserID    
   FROM MONEY_TRANSFER_PAYIN where DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate and    
   ModOfPayment='Chargeback'    
   order by CREATED_BY,TRANSACTIONID desc;    
     
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_REQUESTTRANSACTION_LIST`;
CREATE PROCEDURE `USP_REQUESTTRANSACTION_LIST`(
    IN v_CREATED_BY VARCHAR(10)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	
	SELECT TRANSACTIONID
      ,AMOUNT
      ,(case when STATUS<>'Pending' then 'Success' else STATUS end ) STATUS
      ,DATE_FORMAT(CREATED_ON, '%d/%m/%Y') Requestdate
      ,(case when PortalName is null then 'Payout/transfer' else PortalName end ) servicename
      ,ModOfPayment
      ,user_order_id ReferenceId
	  FROM MONEY_TRANSFER where CREATED_BY=v_CREATED_BY and PortalName in('Request For Add Amount','Return Hold Amount')
	  order by TRANSACTIONID desc;
	
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_SELECT_SLABS`;
CREATE PROCEDURE `USP_SELECT_SLABS`(
    IN v_slabfrom VARCHAR(50),
    IN v_service VARCHAR(50),
    IN v_mode VARCHAR(50)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
	select slabfrom
           ,slabto
           ,service
           ,mode
           ,charges
		   from
		   Slab_Master where slabfrom>=v_slabfrom and slabto<=v_slabfrom and service=v_service and mode=v_mode;
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_SENDER_NSERT`;
CREATE PROCEDURE `USP_SENDER_NSERT`(
    IN v_Sender_Mobile VARCHAR(15),
    IN v_Name VARCHAR(1000),
    IN v_Created_By VARCHAR(50),
    IN v_Status VARCHAR(50),
    IN v_OTP VARCHAR(50)
)
BEGIN
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN

	IF (((select COUNT(*) from Sender_Master where Sender_Mobile=v_Sender_Mobile and Status='Active')>=1)) THEN
  SELECT 'This mobile number already exist' as message,0 transactionID;
  ELSE
  SELECT 'This mobile number not Active.' as message,1 transactionID;
  END IF;
	
  END;

	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
	

	IF (((select COUNT(*) from Sender_Master where Sender_Mobile=v_Sender_Mobile and Status='Active')>=1)) THEN
  SELECT 'This mobile number already exist' as message,0 transactionID;
  ELSE
  IF ((v_Name IS NOT NULL)  AND LEN(v_Name)>0) THEN
    INSERT INTO Sender_Master
           (Sender_Mobile
		   ,Name
           ,Created_By
           ,Created_On
           ,Status
           ,OTP)
     VALUES
           (v_Sender_Mobile
		   ,v_Name
           ,v_Created_By
           ,NOW()
           ,v_Status
           ,v_OTP)
		   ;SELECT 'OTP has been send your mobile number' as message,0 transactionID;
    ELSE
    SELECT 'Sender name is blank' as message,1 transactionID;
    END IF;
  END IF;
	

END;
END
$$

DROP PROCEDURE IF EXISTS `USP_SENDER_Resend_OTP`;
CREATE PROCEDURE `USP_SENDER_Resend_OTP`(
    IN v_Sender_Mobile VARCHAR(15),
    IN v_OTP VARCHAR(50)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	
	DECLARE v_otpCheck varchar(10);
    # Insert statements for procedure here
	#set v_otpCheck=(select otp from Sender_Master  WHERE Sender_Mobile=v_Sender_Mobile)
	#if	(v_otpCheck<>'string' and v_otpCheck<>'' and v_otpCheck<>'0' and v_otpCheck<>'123456'
	

	UPDATE Sender_Master SET OTP=v_OTP WHERE Sender_Mobile=v_Sender_Mobile

	;SELECT 'OTP has been send your mobile number' as message,0 transactionID;
	
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_SENDER_UPDATE_OTP`;
CREATE PROCEDURE `USP_SENDER_UPDATE_OTP`(
    IN v_Sender_Mobile VARCHAR(15),
    IN v_OTP VARCHAR(50)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	
	DECLARE v_otpCheck varchar(10);
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN

		SELECT 'OTP is invalid' as message,0 transactionID;
	
  END;

    # Insert statements for procedure here
	#set v_otpCheck=(select otp from Sender_Master  WHERE Sender_Mobile=v_Sender_Mobile)
	#if	(v_otpCheck<>'string' and v_otpCheck<>'' and v_otpCheck<>'0' and v_otpCheck<>'123456'
	


	UPDATE Sender_Master SET Status='Active' WHERE Sender_Mobile=v_Sender_Mobile AND  OTP=v_OTP
	;IF (((select count(*) from Sender_Master WHERE Sender_Mobile=v_Sender_Mobile AND  OTP=v_OTP and Status='Active')>=1)) THEN
  SELECT 'True' as message,0 transactionID;
  ELSE
  SELECT 'OTP is invalid' as message,0 transactionID;
  END IF; 
	
	
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_SENT_SMS_COLLECTION`;
CREATE PROCEDURE `USP_SENT_SMS_COLLECTION`(
    IN v_sms_txt LONGTEXT,
    IN v_mobile DECIMAL(10,0),
    IN v_email VARCHAR(100),
    IN v_otp INT,
    IN v_userid INT,
    IN v_send_st INT,
    IN v_order_id LONGTEXT
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	
	DECLARE v_cnt int;
    # Insert statements for procedure here
	SELECT COUNT(1) INTO v_cnt FROM sms_order WHERE mobile=v_mobile AND userid=v_userid AND CAST(create_on AS date)=CAST(NOW() AS date) LIMIT 1;
	IF (v_cnt<3) THEN
  INSERT INTO sms_order
           (sms_txt
           ,mobile
           ,email
           ,otp
           ,userid
           ,cnt
           ,create_on
           ,send_st
           ,order_id)
     VALUES
           (v_sms_txt
           ,v_mobile
		   ,v_email
           ,v_otp
		   ,v_userid
           ,v_cnt
           ,NOW()
           ,v_send_st
           ,v_order_id);
		   SELECT 'TRUE' TRANSACTIONID;
  ELSE
  SELECT 'we are unable to proccess this request,you have reached maximum limit of transaction for this user.' TRANSACTIONID;
  END IF;
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_SLABS_List`;
CREATE PROCEDURE `USP_SLABS_List`(
    
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
	select id,slabfrom
           ,slabto
           ,service
           ,mode
           ,charges
		   from
		   Slab_Master; 
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_TAX_List`;
CREATE PROCEDURE `USP_TAX_List`(
    IN v_UserID VARCHAR(100)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Select statements for procedure here
	SELECT IFNULL(cast(Tax AS CHAR),'0') rate  FROM User_Tax_Mst where UserID=v_UserID;
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_TRANS_LIST_IN`;
CREATE PROCEDURE `USP_TRANS_LIST_IN`(
    IN v_CREATED_BY VARCHAR(10),
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10),
    IN v_txtTrans VARCHAR(100)
)
BEGIN
BEGIN      
 # added to prevent extra result sets from      
 # interfering with SELECT statements.      
 DECLARE v_benid varchar(10) DEFAULT '0';      
 IF (v_txtTrans='0') THEN
  select   CREATED_ON,user_order_id TRANSACTIONID,UTR ,      
  case when TAX='0' then Amount else '0' end CR      
  ,case when TAX!='0' then AMOUNT else '0' end  Settlement      
  ,STATUS Fee, Total_CD_Amt as  Balance,      
  TRANSACTIONID refno      
  ,UPDATED_ON       
  from MONEY_TRANSFER_PAYIN where CREATED_BY=v_CREATED_BY and ModOfPayment in('Settlement','collection')      
  #and  IFNULL(UTR,'0')<>'0'      
  and STATUS in('paid','Success','processing','SUCCESS','CREATED')       
      
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate      
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate      
 order by UPDATED_ON desc;
  ELSE
  select   CREATED_ON,user_order_id TRANSACTIONID,UTR ,      
  case when TAX='0' then Amount else '0' end CR      
  ,case when TAX!='0' then AMOUNT else '0' end  Settlement      
  ,STATUS Fee,Total_CD_Amt as  Balance,      
  TRANSACTIONID refno      
  ,UPDATED_ON       
  FROM MONEY_TRANSFER_PAYIN where #CREATED_BY=v_CREATED_BY       
# AND CREATED_ON>=v_fromdate      
# AND CREATED_ON<=v_todate       
#and       
ModOfPayment in('Settlement','collection')      
  #and  IFNULL(UTR,'0')<>'0'      
 and (user_order_id=v_txtTrans or TRANSACTIONID= v_txtTrans or AMOUNT=v_txtTrans or UTR=v_txtTrans)       
 order by CREATED_ON desc;
  END IF;      
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_TRANS_LIST_IN_PG`;
CREATE PROCEDURE `USP_TRANS_LIST_IN_PG`(
    IN v_CREATED_BY VARCHAR(10),
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10),
    IN v_txtTrans VARCHAR(100)
)
BEGIN
BEGIN        
 # added to prevent extra result sets from        
 # interfering with SELECT statements.        
 DECLARE v_benid varchar(10) DEFAULT '0';        
 IF (v_txtTrans='0') THEN
  select   CREATED_ON,user_order_id TRANSACTIONID,UTR ,        
  case when TAX='0' then Amount else '0' end CR        
  ,case when TAX!='0' then AMOUNT else '0' end  Settlement        
  ,STATUS Fee, Total_CD_Amt as  Balance,        
  TRANSACTIONID refno        
  ,UPDATED_ON         
  from MONEY_TRANSFER_PAYIN_PG where CREATED_BY=v_CREATED_BY and ModOfPayment in('Settlement','collection')        
  #and  IFNULL(UTR,'0')<>'0'        
  and STATUS in('paid','Success','processing','SUCCESS','CREATED')         
        
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate        
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate        
 order by UPDATED_ON desc;
  ELSE
  select   CREATED_ON,user_order_id TRANSACTIONID,UTR ,        
  case when TAX='0' then Amount else '0' end CR        
  ,case when TAX!='0' then AMOUNT else '0' end  Settlement        
  ,STATUS Fee,Total_CD_Amt as  Balance,        
  TRANSACTIONID refno        
  ,UPDATED_ON         
  FROM MONEY_TRANSFER_PAYIN_PG where #CREATED_BY=v_CREATED_BY         
# AND CREATED_ON>=v_fromdate        
# AND CREATED_ON<=v_todate         
#and         
ModOfPayment in('Settlement','collection')        
  #and  IFNULL(UTR,'0')<>'0'        
 and (user_order_id=v_txtTrans or TRANSACTIONID= v_txtTrans or AMOUNT=v_txtTrans or UTR=v_txtTrans)         
 order by CREATED_ON desc;
  END IF;        
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_TRANS_LIST_OUT`;
CREATE PROCEDURE `USP_TRANS_LIST_OUT`(
    IN v_CREATED_BY VARCHAR(10),
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10),
    IN v_txtTrans VARCHAR(100),
    IN v_status VARCHAR(20)
)
BEGIN
BEGIN        
 # added to prevent extra result sets from        
 # interfering with SELECT statements.        
 DECLARE v_benid varchar(10) DEFAULT '0';        
 IF (v_txtTrans='0') THEN
  select CREATED_ON,user_order_id TRANSACTIONID,UTR         
 ,case when STATUS='Refund' then Amount else '0' end CR        
  ,case when TAX!='0' and STATUS!='Refund' then Amount else '0' end DR        
  ,case when TAX='0' then AMOUNT else '0' end  Recharge        
  ,TAX Fee, Total_CD_Amt as Balance,        
  case when Status='queued' then 'QUEUED' else Status end Status,        
  TRANSACTIONID refno        
  ,UPDATED_ON        
  ,Reason        
  FROM MONEY_TRANSFER_PAYOUT where CREATED_BY=v_CREATED_BY         
   and ModOfPayment not in('Settlement','collection')        
 #and STATUS='Success'        
 #AND CREATED_ON>=v_fromdate        
 #AND CREATED_ON<=v_todate         
and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate        
 and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate        
and STATUS= (case v_status WHEN '0' THEN STATUS  ELSE v_status END)       
 order by  CREATED_ON desc;
  ELSE
  select CREATED_ON,user_order_id TRANSACTIONID,UTR         
 ,case when STATUS='Refund' then Amount else '0' end CR        
  ,case when TAX!='0' and STATUS!='Refund' then Amount else '0' end DR        
  ,case when TAX='0' then AMOUNT else '0' end  Recharge        
  ,TAX Fee, Total_CD_Amt  as Balance,        
  Status,        
  TRANSACTIONID refno        
  ,UPDATED_ON        
  ,Reason        
          
  FROM MONEY_TRANSFER_PAYOUT where CREATED_BY=v_CREATED_BY         
 and ModOfPayment not in('Settlement','collection')        
       
 and (user_order_id=v_txtTrans or TRANSACTIONID= v_txtTrans or UTR =v_txtTrans)         
 order by Balance asc;
  END IF;        
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_TRANS_PAY_IN_AGENT`;
CREATE PROCEDURE `USP_TRANS_PAY_IN_AGENT`(
    IN v_CREATED_BY VARCHAR(10),
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10),
    IN v_txtTrans VARCHAR(100)
)
BEGIN
BEGIN  
 # added to prevent extra result sets from  
 # interfering with SELECT statements.  
 DECLARE v_benid varchar(10) DEFAULT '0';  
 IF (v_txtTrans='0') THEN
  select   CREATED_ON,user_order_id TRANSACTIONID,UTR ,  
  case when TAX='0' then Amount else '0' end CR  
  ,case when TAX!='0' then AMOUNT else '0' end  Settlement  
  ,TAX Fee,cast(Total_CD_Amt AS DECIMAL) Balance,  
  TRANSACTIONID refno  
  ,UPDATED_ON   
  from MONEY_TRANSFER_PAYIN where CREATED_BY in(select userid from UserMaster where agentid=v_CREATED_BY) and ModOfPayment in('Settlement','collection')  
  
  and STATUS in('paid','Success','processing','SUCCESS')   
  
  and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate  
  and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate  
 order by UPDATED_ON desc;
  ELSE
  select   CREATED_ON,user_order_id TRANSACTIONID,UTR ,  
  case when TAX='0' then Amount else '0' end CR  
  ,case when TAX!='0' then AMOUNT else '0' end  Settlement  
  ,TAX Fee,cast(Total_CD_Amt AS DECIMAL) Balance,  
  TRANSACTIONID refno  
  ,UPDATED_ON   
  FROM MONEY_TRANSFER_PAYIN where CREATED_BY=v_CREATED_BY   
# AND CREATED_ON>=v_fromdate  
# AND CREATED_ON<=v_todate   
 and (user_order_id=v_txtTrans or cast(TRANSACTIONID AS CHAR)= v_txtTrans or AMOUNT=v_txtTrans)   
 order by CREATED_ON desc;
  END IF;  
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_TRANS_PAY_OUT_AGENT`;
CREATE PROCEDURE `USP_TRANS_PAY_OUT_AGENT`(
    IN v_CREATED_BY VARCHAR(10),
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10),
    IN v_txtTrans VARCHAR(100)
)
BEGIN
BEGIN  
 # added to prevent extra result sets from  
 # interfering with SELECT statements.  
 DECLARE v_benid varchar(10) DEFAULT '0';  
 IF (v_txtTrans='0') THEN
  select CREATED_ON,user_order_id TRANSACTIONID,UTR   
 ,case when STATUS='Refund' then Amount else '0' end CR  
  ,case when TAX!='0' and STATUS!='Refund' then Amount else '0' end DR  
  ,case when TAX='0' then AMOUNT else '0' end  Recharge  
  ,TAX Fee, cast(Total_CD_Amt AS DECIMAL) Balance,  
  case when Status='queued' then 'Success' else Status end Status,  
  TRANSACTIONID refno  
  ,UPDATED_ON  
  ,Reason  
  FROM MONEY_TRANSFER_PAYOUT where CREATED_BY in(select UserId from UserMaster where AgentID=v_CREATED_BY)  
   and ModOfPayment not in('Settlement','collection')  
 #and STATUS='Success'  
 #AND CREATED_ON>=v_fromdate  
 #AND CREATED_ON<=v_todate   
and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate  
 and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate  
   
 order by  CREATED_ON desc;
  ELSE
  select CREATED_ON,user_order_id TRANSACTIONID,UTR   
 ,case when STATUS='Refund' then Amount else '0' end CR  
  ,case when TAX!='0' and STATUS!='Refund' then Amount else '0' end DR  
  ,case when TAX='0' then AMOUNT else '0' end  Recharge  
  ,TAX Fee, cast(Total_CD_Amt AS DECIMAL) Balance,  
  Status,  
  TRANSACTIONID refno  
  ,UPDATED_ON  
  ,Reason  
    
  FROM MONEY_TRANSFER_PAYOUT where CREATED_BY=v_CREATED_BY   
 and ModOfPayment not in('Settlement','collection')  
# and STATUS='Success'  
 and (user_order_id=v_txtTrans or TRANSACTIONID = v_txtTrans)   
 order by Balance asc;
  END IF;  
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_TRANSACTION_LIST`;
CREATE PROCEDURE `USP_TRANSACTION_LIST`(
    IN v_CREATED_BY VARCHAR(10),
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10),
    IN v_txtTrans VARCHAR(100)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	DECLARE v_benid varchar(10) DEFAULT '0';
	IF (v_txtTrans='0') THEN
  SELECT user_order_id TRANSACTIONID
     ,CREATED_ON
      ,case when modofpayment='Collection' then Amount else '0' end Cr
	  ,case when modofpayment<>'Collection' then Amount else '0' end Dr
	  ,TAX
	  ,case when modofpayment='Collection' or modofpayment='Settlement' then Total_CD_amt
	  else Total_CD_amt end INOutAmt
      ,AccountNo
	  ,HolderName
	  ,BankName
	  ,IFSC
	  #,(case when STATUS<>'Pending' then 'Success' else STATUS end ) STATUS
	   ,(case when CREATED_BY in('10021','10022') then IFNULL(STATUS,'Created') else 'Success' end ) STATUS
      #,(case when modofpayment='Collection' then 'PayIn' else 'PayOut' end ) servicename
	  ,(case when modofpayment='Collection' then 'PayIn' else case when PortalName='Request For Add Amount' then 'Request For Add Amount' else 'PayOut' end end) servicename
      ,ModOfPayment
      ,TRANSACTIONID ReferenceId
	  ,UTR
	  ,(select Name from UserMaster where UserID=v_CREATED_BY) CREATED_BY
	 
  FROM MONEY_TRANSFER where CREATED_BY=v_CREATED_BY 
 #AND CREATED_ON>=v_fromdate
 #AND CREATED_ON<=v_todate 
and  DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate
 and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate
	order by ReferenceId desc;
  ELSE
  SELECT user_order_id TRANSACTIONID
     ,CREATED_ON
      ,case when modofpayment='Collection' then Amount else '0' end Cr
	  ,case when modofpayment<>'Collection' then Amount else '0' end Dr
	  ,TAX
	  ,case when modofpayment='Collection' or modofpayment='Settlement' then Total_CD_amt
	  else Total_CD_amt end INOutAmt
      ,AccountNo
	  ,HolderName
	  ,BankName
	  ,IFSC
	  #,(case when STATUS<>'Pending' then 'Success' else STATUS end ) STATUS
	   ,(case when CREATED_BY in('10021','10022') then IFNULL(STATUS,'Created') else 'Success' end ) STATUS
      #,(case when modofpayment='Collection' then 'PayIn' else 'PayOut' end ) servicename
	  ,(case when modofpayment='Collection' then 'PayIn' else case when PortalName='Request For Add Amount' then 'Request For Add Amount' else 'PayOut' end end) servicename
      ,ModOfPayment
      ,TRANSACTIONID ReferenceId
	  ,UTR
	  ,(select Name from UserMaster where UserID=v_CREATED_BY) CREATED_BY
	 
  FROM MONEY_TRANSFER where CREATED_BY=v_CREATED_BY 
# AND CREATED_ON>=v_fromdate
# AND CREATED_ON<=v_todate 
 and (user_order_id=v_txtTrans or cast(TRANSACTIONID AS CHAR)= v_txtTrans) 
	order by ReferenceId desc;
  END IF;
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_TRANSACTION_LIST_ForAdmin`;
CREATE PROCEDURE `USP_TRANSACTION_LIST_ForAdmin`(
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10),
    IN v_type INT,
    IN v_userType INT,
    IN v_status VARCHAR(15),
    IN v_AgentID INT
)
BEGIN
BEGIN              
 # added to prevent extra result sets from              
 # interfering with SELECT statements.       
       
      
 DECLARE v_benid varchar(10) DEFAULT '0';           
 IF ((v_type=1)) THEN
  SELECT user_order_id TRANSACTIONID              
      ,AMOUNT              
      ,TAX              
      ,TAX_AMOUNT              
      ,TOTAL_AMOUNT              
      ,m.AccountNo              
    ,m.BankName              
   ,m.IFSC              
   ,HolderName              
      ,MobileNo              
   ,IFNULL(UTR,'0') UTR              
      ,m.STATUS STATUS              
      ,CREATED_ON              
      ,Total_CD_Amt as  servicename              
      ,ModOfPayment              
      ,TRANSACTIONID ReferenceId              
   ,u.Name  CREATED_BY              
 ,u.UserId              
  FROM MONEY_TRANSFER_PAYIN m inner join UserMaster u on m.CREATED_BY=u.UserId              
  where   DATE_FORMAT(m.CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate              
 and DATE_FORMAT(m.CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate          
 and m.CREATED_BY= (case v_userType WHEN 0 THEN m.CREATED_BY  ELSE v_userType END)      
  and m.STATUS= (case v_status WHEN '0' THEN m.STATUS  ELSE v_status END)      
  #and m.STATUS in('processed','SUCCESS','PENDING','FAILED','REFUND')               
 order by ReferenceId desc;
  END IF;     
      
     
IF ((v_type=2)) THEN
  SELECT user_order_id TRANSACTIONID              
      ,AMOUNT              
      ,TAX              
      ,TAX_AMOUNT              
      ,TOTAL_AMOUNT              
      ,m.AccountNo              
    ,m.BankName              
   ,m.IFSC              
   ,HolderName              
      ,MobileNo              
   ,IFNULL(UTR,'0') UTR              
      ,m.STATUS STATUS              
      ,CREATED_ON              
      ,Total_CD_Amt as  servicename              
      ,ModOfPayment              
      ,TRANSACTIONID ReferenceId              
   ,u.Name  CREATED_BY              
 ,u.UserId              
  FROM MONEY_TRANSFER_PAYOUT m inner join UserMaster u on m.CREATED_BY=u.UserId              
  where   DATE_FORMAT(m.CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate              
 and DATE_FORMAT(m.CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate      
    and u.AgentID= (case  WHEN v_AgentID = 0 THEN u.AgentID  ELSE v_AgentID END)    
  and m.CREATED_BY= (case  WHEN v_userType = 0 THEN m.CREATED_BY  ELSE v_userType END)      
  and m.STATUS= (case v_status WHEN '0' THEN m.STATUS  ELSE v_status END)      
    #and m.STATUS in('processed','SUCCESS','PENDING','FAILED','REFUND')              
 order by ReferenceId desc;
  END IF;      
      
 END;
END
$$

DROP PROCEDURE IF EXISTS `USP_TRANSACTION_LIST_ForAdmin_1`;
CREATE PROCEDURE `USP_TRANSACTION_LIST_ForAdmin_1`(
    IN v_fromdate VARCHAR(10),
    IN v_todate VARCHAR(10),
    IN v_txtTrans VARCHAR(200),
    IN v_trType INT,
    IN v_userID INT,
    IN v_limit INT,
    IN v_offset INT
)
BEGIN
BEGIN

	# added to prevent extra result sets from
	# interfering with SELECT statements.
	DECLARE v_benid varchar(10) DEFAULT '0';
  DECLARE v_offset_calc_h INT;
  SET v_offset_calc_h = ((v_limit-1)*v_offset);
  
	IF (v_txtTrans='0') THEN
  SELECT user_order_id TRANSACTIONID
      ,AMOUNT
      ,TAX
      ,TAX_AMOUNT
      ,TOTAL_AMOUNT
      ,m.AccountNo
	   ,BankName
	  ,m.IFSC
	  ,HolderName
      ,MobileNo
	  ,UTR
      ,m.STATUS STATUS
      ,CREATED_ON
      ,(case when PortalName is null then 'Payout/transfer' else PortalName end ) servicename
      ,ModOfPayment
      ,TRANSACTIONID ReferenceId
	  ,u.Name  CREATED_BY
	,u.UserId
  FROM MONEY_TRANSFER m inner join UserMaster u on m.CREATED_BY=u.UserId
  where DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate
  and ((v_trType  = 0 and m.ModOfPayment=m.ModOfPayment)
    or (v_trType  = 1 and  m.ModOfPayment in  ('Settlement','collection'))
	or (v_trType  = 2 and  m.ModOfPayment not in ('Settlement','collection'))
	)
 and  (( v_userID  = 0 and u.UserId=u.UserId)
    or (v_userID  <> 0 and  u.UserId = v_userID)
	)
 and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate and  
  m.STATUS in('processed','SUCCESS')    
 order by ReferenceId desc LIMIT v_limit OFFSET v_offset_calc_h;
  END IF;
 IF (v_txtTrans='1') THEN
  SELECT user_order_id TRANSACTIONID
      ,AMOUNT
      ,TAX
      ,TAX_AMOUNT
      ,TOTAL_AMOUNT
      ,m.AccountNo
	   ,BankName
	  ,m.IFSC
	  ,HolderName
      ,MobileNo
	  ,UTR
      ,m.STATUS STATUS
      ,CREATED_ON
      ,(case when PortalName is null then 'Payout/transfer' else PortalName end ) servicename
      ,ModOfPayment
      ,TRANSACTIONID ReferenceId
	  ,u.Name  CREATED_BY
	,u.UserId
  FROM MONEY_TRANSFER m inner join UserMaster u on m.CREATED_BY=u.UserId
  where DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')>=v_fromdate
  and ((v_trType  = 0 and m.ModOfPayment=m.ModOfPayment)
    or (v_trType  = 1 and  m.ModOfPayment in  ('Settlement','collection'))
	or (v_trType  = 2 and  m.ModOfPayment not in ('Settlement','collection'))
	)
 and  (( v_userID  = 0 and u.UserId=u.UserId)
    or (v_userID  <> 0 and  u.UserId = v_userID)
	)
 and DATE_FORMAT(CREATED_ON, '%Y-%m-%dT%H:%i:%s')<=v_todate and  
  m.STATUS in('processed','SUCCESS')    
 order by ReferenceId desc;
  ELSE
  SELECT user_order_id TRANSACTIONID
      ,AMOUNT
      ,TAX
      ,TAX_AMOUNT
      ,TOTAL_AMOUNT
      ,m.AccountNo
	   ,BankName
	  ,m.IFSC
	  ,HolderName
      ,MobileNo
	  ,UTR
      ,m.STATUS STATUS
      ,CREATED_ON
      ,(case when PortalName is null then 'Payout/transfer' else PortalName end ) servicename
      ,ModOfPayment
      ,TRANSACTIONID ReferenceId
	  ,u.Name  CREATED_BY
	,u.UserId
  FROM MONEY_TRANSFER m inner join UserMaster u on m.CREATED_BY=u.UserId
  where  (m.user_order_id=v_txtTrans or cast(m.TRANSACTIONID AS CHAR)= v_txtTrans or m.AMOUNT=v_txtTrans or cast(m.UTR AS CHAR)=v_txtTrans) 
 and m.STATUS in('processed','SUCCESS') 
 order by ReferenceId desc;
  END IF;


	
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_UPDATE_HOLDING_AMT`;
CREATE PROCEDURE `USP_UPDATE_HOLDING_AMT`(
    IN v_UserId INTEGER,
    IN v_Hold_Amt DECIMAL(18,0)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
	UPDATE UserMaster SET Hold_Amt=(IFNULL(Hold_Amt,0)+v_Hold_Amt),Collection_Amount=(Collection_Amount-v_Hold_Amt)
		   where UserId=v_UserId; 
           		  
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

DROP PROCEDURE IF EXISTS `USP_UPDATE_USER_API_DETAILS`;
CREATE PROCEDURE `USP_UPDATE_USER_API_DETAILS`(
    IN v_APIID INT,
    IN v_UserId INT,
    IN v_Status VARCHAR(10),
    IN v_FromDate DATETIME,
    IN v_Service_Amount INT,
    IN v_Service_Amount_Status VARCHAR(10),
    IN v_ToDate DATETIME,
    IN v_ModifyOn DATETIME,
    IN v_ModifyBy INT
)
BEGIN
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN

		select 'FAILLED' as message,0 STATUS;
	
  END;

	# added to prevent extra result sets from
	# interfering with SELECT statements.
	

    # Insert statements for procedure here
	

	UPDATE UserAPIDetails
   SET Status = v_Status
      ,FromDate = v_FromDate
      ,ToDate = v_ToDate
      ,ModifyBy = v_ModifyBy
      ,ModifyOn = v_ModifyOn
	  ,Service_Amount=v_Service_Amount
	  ,Service_Amount_Status=v_Service_Amount_Status
 WHERE UserId=v_UserId and APIID=v_APIID

	;select 'SUCCESS' as message,1 STATUS;
	
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_USER_CHANGE_PASSWORD`;
CREATE PROCEDURE `USP_USER_CHANGE_PASSWORD`(
    IN v_UserId INT,
    IN v_Password VARCHAR(50),
    IN v_OldPassword VARCHAR(50),
    OUT v_loginStatus INT
)
BEGIN
BEGIN


	
	DECLARE v_oldPasswordInner varchar(50);
	set v_oldPasswordInner = (select Password  from UserMaster  where UserId=v_UserId);
    # Insert statements for procedure here
	IF ((v_oldPasswordInner = v_OldPassword)) THEN
  UPDATE UserMaster
   SET Password = v_Password
 WHERE UserId=v_UserId
  ;SET v_loginStatus = 1;
  ELSE
  IF ((v_oldPasswordInner != v_OldPassword)) THEN
    SET v_loginStatus = 2;
    ELSE
    SET v_loginStatus = 3;
    END IF;
  END IF;END;
END
$$

DROP PROCEDURE IF EXISTS `USP_USER_CHANGE_PASSWORD_WITH_OLD`;
CREATE PROCEDURE `USP_USER_CHANGE_PASSWORD_WITH_OLD`(
    IN v_UserId INT,
    IN v_Password VARCHAR(50),
    IN v_oldPassword VARCHAR(100),
    OUT v_outFlag INT
)
BEGIN
DECLARE v_OldPasswordInner varchar(100);
BEGIN

	# added to prevent extra result sets from
	# interfering with SELECT statements.
	
	set v_OldPasswordInner =(select a.Password from UserMaster a where a.UserId=v_UserId) 
	;IF ((v_OldPasswordInner = v_oldPassword)) THEN
  UPDATE UserMaster
   SET Password = v_Password
 WHERE UserId=v_UserId;
	 SET v_outFlag = 0;
  ELSE
  SET v_outFlag = 1;
  END IF;
    # Insert statements for procedure here
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_USER_PAYOUT`;
CREATE PROCEDURE `USP_USER_PAYOUT`(
    IN v_trFlag INT,
    IN v_txtTrans VARCHAR(500),
    IN v_txtUTR VARCHAR(30),
    IN v_userID INT
)
BEGIN
BEGIN    
 DECLARE v_benid varchar(10) DEFAULT '0';    
IF ((v_trFlag =1)) THEN
  IF ((v_txtTrans !='')) THEN
    Select CREATED_ON,user_order_id TRANSACTIONID,UTR   
 ,case when STATUS='Refund' then Amount else '0' end CR  
  ,case when TAX!='0' and STATUS!='Refund' then Amount else '0' end DR  
  ,case when TAX='0' then AMOUNT else '0' end  Recharge  
  ,TAX Fee, cast(Total_CD_Amt AS DECIMAL) Balance,  
  case when Status='queued' then 'Success' else Status end Status,  
  TRANSACTIONID refno  
  ,UPDATED_ON  
  ,Reason  
  FROM MONEY_TRANSFER_PAYIN m  
  where (m.TRANSACTIONID=v_txtTrans or m.user_order_id = v_txtTrans ) and m.CREATED_BY=v_userID and m.STATUS in('paid','Success','processing','SUCCESS','CREATED');
    ELSE
    select CREATED_ON,user_order_id TRANSACTIONID,UTR   
 ,case when STATUS='Refund' then Amount else '0' end CR  
  ,case when TAX!='0' and STATUS!='Refund' then Amount else '0' end DR  
  ,case when TAX='0' then AMOUNT else '0' end  Recharge  
  ,TAX Fee, cast(Total_CD_Amt AS DECIMAL) Balance,  
  case when Status='queued' then 'Success' else Status end Status,  
  TRANSACTIONID refno  
  ,UPDATED_ON  
  ,Reason  
  FROM MONEY_TRANSFER_PAYIN m   
  where  UTR=v_txtUTR and m.CREATED_BY=v_userID and m.STATUS in('paid','Success','processing','SUCCESS','CREATED');
    END IF;
  END IF;  
  
IF ((v_trFlag =2)) THEN
  IF ((v_txtTrans !='')) THEN
    Select CREATED_ON,user_order_id TRANSACTIONID,UTR   
 ,case when STATUS='Refund' then Amount else '0' end CR  
  ,case when TAX!='0' and STATUS!='Refund' then Amount else '0' end DR  
  ,case when TAX='0' then AMOUNT else '0' end  Recharge  
  ,TAX Fee, cast(Total_CD_Amt AS DECIMAL) Balance,  
  case when Status='queued' then 'Success' else Status end Status,  
  TRANSACTIONID refno  
  ,UPDATED_ON  
  ,Reason  
  FROM MONEY_TRANSFER_PAYOUT m  
  where (m.TRANSACTIONID=v_txtTrans or m.user_order_id = v_txtTrans ) and m.CREATED_BY=v_userID and m.STATUS in('paid','Success','processing','SUCCESS','CREATED');
    ELSE
    select CREATED_ON,user_order_id TRANSACTIONID,UTR   
 ,case when STATUS='Refund' then Amount else '0' end CR  
  ,case when TAX!='0' and STATUS!='Refund' then Amount else '0' end DR  
  ,case when TAX='0' then AMOUNT else '0' end  Recharge  
  ,TAX Fee, cast(Total_CD_Amt AS DECIMAL) Balance,  
  case when Status='queued' then 'Success' else Status end Status,  
  TRANSACTIONID refno  
  ,UPDATED_ON  
  ,Reason  
  FROM MONEY_TRANSFER_PAYOUT m   
  where  UTR=v_txtUTR and m.CREATED_BY=v_userID and m.STATUS in('paid','Success','processing','SUCCESS','CREATED');
    END IF;
  END IF;  
  
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_USER_PAYOUT_ADMIN`;
CREATE PROCEDURE `USP_USER_PAYOUT_ADMIN`(
    IN v_trFlag INT,
    IN v_txtTrans VARCHAR(500),
    IN v_txtUTR VARCHAR(30)
)
BEGIN
BEGIN        
 DECLARE v_benid varchar(10) DEFAULT '0';        
IF ((v_trFlag =1)) THEN
  IF ((v_txtTrans !='')) THEN
    Select m.CREATED_ON,m.user_order_id TRANSACTIONID,m.UTR       
 ,case when m.STATUS='Refund' then m.Amount else '0' end CR      
  ,case when TAX!='0' and m.STATUS!='Refund' then Amount else '0' end DR      
  ,case when TAX='0' then m.AMOUNT else '0' end  Recharge      
  ,m.TAX Fee, cast(m.Total_CD_Amt AS DECIMAL) Balance,      
  case when m.Status='queued' then 'Success' else m.Status end Status,      
  m.TRANSACTIONID refno      
  ,m.UPDATED_ON      
  ,m.Reason    
  ,m.AMOUNT as Amt
  ,us.Callback_URL as Callback_URL
  FROM MONEY_TRANSFER_PAYIN m  Join UserMaster us on m.CREATED_BY = us.UserId    
  where (m.TRANSACTIONID=v_txtTrans or m.user_order_id = v_txtTrans ) and m.STATUS in('paid','Success','processing','SUCCESS','CREATED');
    ELSE
    Select m.CREATED_ON,m.user_order_id TRANSACTIONID,m.UTR       
 ,case when m.STATUS='Refund' then m.Amount else '0' end CR      
  ,case when TAX!='0' and m.STATUS!='Refund' then Amount else '0' end DR      
  ,case when TAX='0' then m.AMOUNT else '0' end  Recharge      
  ,m.TAX Fee, cast(m.Total_CD_Amt AS DECIMAL) Balance,      
  case when m.Status='queued' then 'Success' else m.Status end Status,      
  m.TRANSACTIONID refno      
  ,m.UPDATED_ON      
  ,m.Reason    
  ,m.AMOUNT as Amt
  ,us.Callback_URL as Callback_URL
  FROM MONEY_TRANSFER_PAYIN m  Join UserMaster us on m.CREATED_BY = us.UserId     
  where  UTR=v_txtUTR  and m.STATUS in('paid','Success','processing','SUCCESS','CREATED');
    END IF;
  END IF;      
      
IF ((v_trFlag =2)) THEN
  IF ((v_txtTrans !='')) THEN
    Select m.CREATED_ON,m.user_order_id TRANSACTIONID,m.UTR       
 ,case when m.STATUS='Refund' then m.Amount else '0' end CR      
  ,case when m.TAX!='0' and m.STATUS!='Refund' then m.Amount else '0' end DR      
  ,case when m.TAX='0' then m.AMOUNT else '0' end  Recharge      
  ,m.TAX Fee, cast(m.Total_CD_Amt AS DECIMAL) Balance,      
  case when m.Status='queued' then 'Success' else m.Status end Status,      
  m.TRANSACTIONID refno      
  ,m.UPDATED_ON      
  ,m.Reason    
  ,m.TOTAL_AMOUNT as Amt
   ,us.Callback_URL as Callback_URL
  FROM MONEY_TRANSFER_PAYOUT m  Join UserMaster us on m.CREATED_BY = us.UserId       
  where (m.TRANSACTIONID=v_txtTrans or m.user_order_id = v_txtTrans ) and m.STATUS in('paid','Success','processing','SUCCESS','CREATED');
    ELSE
    select m.CREATED_ON,m.user_order_id TRANSACTIONID,m.UTR       
 ,case when m.STATUS='Refund' then m.Amount else '0' end CR      
  ,case when m.TAX!='0' and m.STATUS!='Refund' then Amount else '0' end DR      
  ,case when m.TAX='0' then m.AMOUNT else '0' end  Recharge      
  ,m.TAX Fee, cast(m.Total_CD_Amt AS DECIMAL) Balance,      
  case when m.Status='queued' then 'Success' else m.Status end Status,      
  TRANSACTIONID refno      
  ,UPDATED_ON      
  ,Reason    
  ,TOTAL_AMOUNT as Amt
    ,us.Callback_URL as Callback_URL
  FROM MONEY_TRANSFER_PAYOUT m  Join UserMaster us on m.CREATED_BY = us.UserId        
  where  UTR=v_txtUTR and m.STATUS in('paid','Success','processing','SUCCESS','CREATED');
    END IF;
  END IF;      
      
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_USER_PAYOUT_PG`;
CREATE PROCEDURE `USP_USER_PAYOUT_PG`(
    IN v_trFlag INT,
    IN v_txtTrans VARCHAR(500),
    IN v_txtUTR VARCHAR(30),
    IN v_userID INT
)
BEGIN
BEGIN      
 DECLARE v_benid varchar(10) DEFAULT '0';      
IF ((v_trFlag =1)) THEN
  IF ((v_txtTrans !='')) THEN
    Select CREATED_ON,user_order_id TRANSACTIONID,UTR     
 ,case when STATUS='Refund' then Amount else '0' end CR    
  ,case when TAX!='0' and STATUS!='Refund' then Amount else '0' end DR    
  ,case when TAX='0' then AMOUNT else '0' end  Recharge    
  ,TAX Fee, cast(Total_CD_Amt AS DECIMAL) Balance,    
  case when Status='queued' then 'Success' else Status end Status,    
  TRANSACTIONID refno    
  ,UPDATED_ON    
  ,Reason    
  FROM MONEY_TRANSFER_PAYIN_PG m    
  where (m.TRANSACTIONID=v_txtTrans or m.user_order_id = v_txtTrans ) and m.CREATED_BY=v_userID and m.STATUS in('paid','Success','processing','SUCCESS','CREATED');
    ELSE
    select CREATED_ON,user_order_id TRANSACTIONID,UTR     
 ,case when STATUS='Refund' then Amount else '0' end CR    
  ,case when TAX!='0' and STATUS!='Refund' then Amount else '0' end DR    
  ,case when TAX='0' then AMOUNT else '0' end  Recharge    
  ,TAX Fee, cast(Total_CD_Amt AS DECIMAL) Balance,    
  case when Status='queued' then 'Success' else Status end Status,    
  TRANSACTIONID refno    
  ,UPDATED_ON    
  ,Reason    
  FROM MONEY_TRANSFER_PAYIN_PG m     
  where  UTR=v_txtUTR and m.CREATED_BY=v_userID and m.STATUS in('paid','Success','processing','SUCCESS','CREATED');
    END IF;
  END IF;    
    
	/*
if(v_trFlag =2)    
begin    
    
if(v_txtTrans !='')    
begin    
    
 Select CREATED_ON,user_order_id TRANSACTIONID,UTR     
 ,case when STATUS='Refund' then Amount else '0' end CR    
  ,case when TAX!='0' and STATUS!='Refund' then Amount else '0' end DR    
  ,case when TAX='0' then AMOUNT else '0' end  Recharge    
  ,TAX Fee, cast(Total_CD_Amt AS DECIMAL) Balance,    
  case when Status='queued' then 'Success' else Status end Status,    
  TRANSACTIONID refno    
  ,UPDATED_ON    
  ,Reason    
  FROM MONEY_TRANSFER_PAYOUT m    
  where (m.TRANSACTIONID=v_txtTrans or m.user_order_id = v_txtTrans ) and m.CREATED_BY=v_userID and m.STATUS in('paid','Success','processing','SUCCESS','CREATED');    
   END    
   else    
    select CREATED_ON,user_order_id TRANSACTIONID,UTR     
 ,case when STATUS='Refund' then Amount else '0' end CR    
  ,case when TAX!='0' and STATUS!='Refund' then Amount else '0' end DR    
  ,case when TAX='0' then AMOUNT else '0' end  Recharge    
  ,TAX Fee, cast(Total_CD_Amt AS DECIMAL) Balance,    
  case when Status='queued' then 'Success' else Status end Status,    
  TRANSACTIONID refno    
  ,UPDATED_ON    
  ,Reason    
  FROM MONEY_TRANSFER_PAYOUT m     
  where  UTR=v_txtUTR and m.CREATED_BY=v_userID and m.STATUS in('paid','Success','processing','SUCCESS','CREATED');      
    
End    
    */
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_WALLET_AMOUNT`;
CREATE PROCEDURE `USP_WALLET_AMOUNT`(
    IN v_UserId INT,
    IN v_Available_Amount DECIMAL(10,2)
)
BEGIN
BEGIN  
 # added to prevent extra result sets from  
 # interfering with SELECT statements.  
 DECLARE v_amount numeric(10,2);  
   
   
 set v_amount=(select Available_Amount from UserMaster WHERE UserId=v_UserId)  

 
 
    # Insert statements for procedure here  
 ;IF (v_amount>v_Available_Amount) THEN
  UPDATE UserMaster  
   SET Available_Amount = Available_Amount-v_Available_Amount  
 WHERE UserId=v_UserId and Available_Amount>v_Available_Amount  
 ;select 1 as status,'1' message;
  ELSE
  select 'Insufficiant balance for this transaction' as message,0 status;
  END IF;  

 
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_WALLET_AMOUNT_Limit`;
CREATE PROCEDURE `USP_WALLET_AMOUNT_Limit`(
    IN v_UserId INT
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	DECLARE v_amount int;
	
	select Available_Amount,IFNULL(Wallet_Limit,0) Wallet_Limit,0 UsedLimit from(
	select Available_Amount,Wallet_Limit from UserMaster WHERE UserId=v_UserId) Balance;
    # Insert statements for procedure here
	
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_WALLET_AMOUNT_Limit_Both`;
CREATE PROCEDURE `USP_WALLET_AMOUNT_Limit_Both`(
    IN v_ClientId VARCHAR(200),
    IN v_ClientSrc VARCHAR(200)
)
BEGIN
BEGIN  
 # added to prevent extra result sets from  
 # interfering with SELECT statements.  
 DECLARE v_amount int;  
   
 
 select  IFNULL(Collection_Amount,0) as PayinAmount,IFNULL(Available_Amount,0) as PayOutAmount,IFNULL(Hold_Amt,0) as HoldingAmount  from UserMaster WHERE ClientID=v_ClientId and ClientSecret= v_ClientSrc  ;
    # Insert statements for procedure here  
   
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_WALLET_AMOUNT_REFUND`;
CREATE PROCEDURE `USP_WALLET_AMOUNT_REFUND`(
    IN v_UserId INT,
    IN v_Available_Amount DECIMAL(10,0)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	DECLARE v_amount int;
	
	
	SELECT Available_Amount INTO v_amount from UserMaster WHERE UserId=v_UserId LIMIT 1;IF (v_amount>=v_Available_Amount) THEN
  UPDATE UserMaster
   SET Available_Amount =Available_Amount+v_Available_Amount
 WHERE UserId=v_UserId 
	;select 1 as status,'1' message;
  ELSE
  select 'Insufficiant balance for this transaction' as message,0 status;
  END IF;
END;
END
$$

DROP PROCEDURE IF EXISTS `USP_WALLET_COLLECTION_AMOUNT`;
CREATE PROCEDURE `USP_WALLET_COLLECTION_AMOUNT`(
    IN v_UserId INT,
    IN v_Available_Amount DECIMAL(10,0)
)
BEGIN
BEGIN
	# added to prevent extra result sets from
	# interfering with SELECT statements.
	DECLARE v_amount numeric(10,0);
	
	
	SELECT Collection_Amount INTO v_amount from UserMaster WHERE UserId=v_UserId LIMIT 1;IF (v_amount>=v_Available_Amount) THEN
  UPDATE UserMaster
   SET Collection_Amount =Collection_Amount-v_Available_Amount,
   Available_Amount =(IFNULL(Available_Amount,0)+(select (v_Available_Amount-((v_Available_Amount*Tax)/100)) from User_Tax_Mst WHERE UserId=v_UserId) )
   WHERE UserId=v_UserId# and Collection_Amount=v_Available_Amount

   

	/*If v_UserId in(10011,10002,10022,10021,10023,10024,10025,10026,10027,10029,10030,10031,10032,10033,10034,10035,10036,10037,10038,10039,10040,10041,10042,10043,10044,10045,10046,10047,10048,10049,10050)
	Begin
	UPDATE UserMaster
   SET Available_Amount =(Available_Amount+v_Available_Amount)
    WHERE UserId=v_UserId; 
	end */
	;select 1 as status,'1' message;
  ELSE
  select 'Insufficiant balance for this transaction' as message,0 status;
  END IF;
END;
END
$$

DROP PROCEDURE IF EXISTS `Verify_Benificiary`;
CREATE PROCEDURE `Verify_Benificiary`(
    IN v_AccountNo VARCHAR(20),
    IN v_BeneName VARCHAR(30),
    IN v_Status VARCHAR(20)
)
BEGIN
BEGIN
IF (v_BeneName is null) THEN
  update AddBeneficiary set Status=v_Status where AccountNo=v_AccountNo;
  ELSE
  update AddBeneficiary set Status=v_Status where AccountNo=v_AccountNo;
  END IF;
update UserMaster set available_amount=(available_amount-4) where userid=
(select distinct AgentId from AddBeneficiary where AccountNo=v_AccountNo)
 
	;select distinct 'Benificiary verified successfully' as message,0 transactionID from  AddBeneficiary;
	
END;
END
$$

DELIMITER ;
