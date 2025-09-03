USE chore_app; 

 

-- Household Table (Users) 

CREATE TABLE Household ( 

    UserID VARCHAR(50) PRIMARY KEY, 

    Email VARCHAR(100) UNIQUE NOT NULL, 

    Name VARCHAR(100) NOT NULL, 

    Password VARCHAR(255) NOT NULL, 

    Score INT NOT NULL DEFAULT 0 

) ENGINE=InnoDB; 

  

-- Reward Table (Create before Chore since Chore references RewardID) 

CREATE TABLE Reward ( 

    RewardID VARCHAR(50) PRIMARY KEY, 

    RewardName VARCHAR(100) NOT NULL, 

    RewardPoint INT NOT NULL 

) ENGINE=InnoDB; 

  

-- Chore Table 

CREATE TABLE Chore ( 

    ChoreID VARCHAR(50) PRIMARY KEY, 

    UID VARCHAR(50), 

    RID VARCHAR(50), 

    Description TEXT, 

    CName VARCHAR(255) NOT NULL, 

    DueDate DATE,                

    DateCompleted DATE DEFAULT NULL, 

    ChoreStatus ENUM('Complete', 'Unassigned', 'In Progress') NOT NULL, 

    FOREIGN KEY (UID) REFERENCES Household(UserID) ON DELETE SET NULL, 

    FOREIGN KEY (RID) REFERENCES Reward(RewardID) ON DELETE SET NULL 

) ENGINE=InnoDB; 

--Database completed  