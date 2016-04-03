set @leagueName   = 'AYSO Region 122';
set @usa          = 'United States';
set @startDate    = '2016-08-16';
set @endDate      = '2016-11-18';
set @startTime    = '03:30:00';
set @endTime      = '07:00:00';
set @practiceDays = '1111100';
set @santaBarbara = 'Santa Barbara';
set @goleta       = 'Goleta';
set @montecito    = 'Montecito';
set @noleta       = 'Between Santa Barbara and Goleta';
set @u5           = 'U5';
set @u6           = 'U6';
set @u7           = 'U7';
set @u8           = 'U8';
set @u9           = 'U9';
set @u10          = 'U10';
set @u11          = 'U11';
set @u12          = 'U12';
set @u14          = 'U14';
set @u16_19       = 'U16-19';

set @facilityName = 'Adams Elementary School';
call sp_addFacility(@leagueName, @facilityName, '2701 Las Positas Rd', '', @santaBarbara, 'CA', '93105', @usa,
    'Amy Alzina', 'aalzina@sbsdk12.org', '(805) 563-2515', 'AdamsElementarySchool.jpg', 0, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Hope Elementary School';
call sp_addFacility(@leagueName, @facilityName, '3970 La Colina Road', '', @santaBarbara, 'CA', '93110', @usa,
    'Julie Martin', 'jmartin@hopeschooldistrict.org', '(805) 563-2974', 'HopeElementarySchoolFields_zpsa1f75f93.jpg', 0, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Monte Vista Elementary School';
call sp_addFacility(@leagueName, @facilityName, '730 North Hope Ave', '', @santaBarbara, 'CA', '93110', @usa,
    'Kim Fatch', 'kfatch@hopeschooldistrict.org', '(805) 687-5333', 'MonteVistaElementarySchoolFields_zpsf1c7d257.jpg', 0, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addFacilityLocation(@leagueName, @facilityName, @noleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Monroe Elementary School';
call sp_addFacility(@leagueName, @facilityName, '431 Flora Vista Drive', '', @santaBarbara, 'CA', '93109', @usa,
    '', '', '(805) 966-7023', 'MonroeElementarySchool_zps1755ec2b.jpg', 0, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Peabody Charter School';
call sp_addFacility(@leagueName, @facilityName, '3018 Calle Noguera', '', @santaBarbara, 'CA', '93105', @usa,
    'Joan Henry', 'JHenry@peabodycharter.org', '(805) 563-1172 x154', 'PeabodyElementarySchoolFields_zps09f429f8.jpg', 0, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Roosevelt Elementary School';
call sp_addFacility(@leagueName, @facilityName, '1990 Laguna Street', '', @santaBarbara, 'CA', '93101', @usa,
    'Christy Mendivil', 'cmendivil@sbsdk12.org', '', 'RooseveltElementarySchool.jpg', 0, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Vieja Valley Elementary School';
call sp_addFacility(@leagueName, @facilityName, '434 Nogal Dr', '', @santaBarbara, 'CA', '93110', @usa,
    'Chelsea Jobes', 'cjopes@hopesdk6.org', '(805) 967-1239', 'ViejaValleyElementary_zps6cbaeb7c.jpg', 0, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addFacilityLocation(@leagueName, @facilityName, @noleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Washington Elementary School';
call sp_addFacility(@leagueName, @facilityName, '290 Lighthouse Road', '', @santaBarbara, 'CA', '93109', @usa,
    'Rosa Cavaletto', 'rcavaletto@sbsdk12.org', '(805) 965-6653', 'WashingtonElementarySchool_zps30a0e0b5.jpg', 0, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addField(@leagueName, @facilityName, 'A', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u5, @facilityName, 'A');
call sp_addDivisionField(@leagueName, @u6, @facilityName, 'A');
call sp_addDivisionField(@leagueName, @u7, @facilityName, 'A');
call sp_addDivisionField(@leagueName, @u8, @facilityName, 'A');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'A', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'East Side Neighborhood Park';
call sp_addFacility(@leagueName, @facilityName, '1258 E Yanonali St', '', @santaBarbara, 'CA', '93103', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'EastsideNeighborhoodPark.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Escandido Neighborhood Park';
call sp_addFacility(@leagueName, @facilityName, '1306 Flora Vista Drive', '', @santaBarbara, 'CA', '', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'EscondidoPark.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'La Mesa Park';
call sp_addFacility(@leagueName, @facilityName, '295 Meigs Road', '', @santaBarbara, 'CA', '93101', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'LaMesaParkFields_zpsb521a856.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u5, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '2');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Chase Palm Park';
call sp_addFacility(@leagueName, @facilityName, '323 E Cabrillo Blvd', '', @santaBarbara, 'CA', '93101', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'ChasePalmParm_zps2a636194.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u5, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '2');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Mission Rose Garden';
call sp_addFacility(@leagueName, @facilityName, 'Plaza Rubio', '', @santaBarbara, 'CA', '93105', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'MissionRoseGarden.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Oak Park';
call sp_addFacility(@leagueName, @facilityName, 'W Junipero and W Alamar', '', @santaBarbara, 'CA', '93105', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'OakPark.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Pilgram Terrace Park';
call sp_addFacility(@leagueName, @facilityName, 'Pilgram Terrace Drive', '', @santaBarbara, 'CA', '93101', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'PilgramTerracePark.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Plaza Vera Cruz Park';
call sp_addFacility(@leagueName, @facilityName, 'E Cota St', '', @santaBarbara, 'CA', '93101', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'PlazaDeVeraCruzPark.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'San Roque Park';
call sp_addFacility(@leagueName, @facilityName, 'Cannon Dr', '', @santaBarbara, 'CA', '93105', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'SanRoquePark.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Los Robles Park';
call sp_addFacility(@leagueName, @facilityName, '4010 Via Diego', '', @santaBarbara, 'CA', '93110', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'LosRoblesPark.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Shoreline Park';
call sp_addFacility(@leagueName, @facilityName, 'Shoreline Dr', '', @santaBarbara, 'CA', '93109', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'ShorelineParkFields_zps66138835.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u5, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '2');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Stevens Park';
call sp_addFacility(@leagueName, @facilityName, 'Canon Dr', '', @santaBarbara, 'CA', '93105', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'StevensPark.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Willow Glen Park';
call sp_addFacility(@leagueName, @facilityName, 'San Roque', '', @santaBarbara, 'CA', '93105', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'WillowGlenParkFields_zpse4a7be52.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u5, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '2');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Elings Park';
call sp_addFacility(@leagueName, @facilityName, '1298 Las Positas Road', '', @santaBarbara, 'CA', '93103', @usa,
    'Christina Franquet', 'cfranquet@elingspark.org', '805-569-5611', 'EilingsParkFields_zpsce2266e1.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, 'L1', 0);
call sp_addField(@leagueName, @facilityName, 'L2', 0);
call sp_addField(@leagueName, @facilityName, 'U1', 0);
call sp_addField(@leagueName, @facilityName, 'U2', 0);
call sp_addField(@leagueName, @facilityName, '1A', 1);
call sp_addField(@leagueName, @facilityName, '1B', 1);
call sp_addField(@leagueName, @facilityName, '2A', 0);
call sp_addField(@leagueName, @facilityName, '2B', 0);
call sp_addField(@leagueName, @facilityName, '3A', 1);
call sp_addField(@leagueName, @facilityName, '3B', 1);
call sp_addDivisionField(@leagueName, @u11, @facilityName, 'L1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, 'L1');
call sp_addDivisionField(@leagueName, @u14, @facilityName, 'L1');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, 'L1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, 'L2');
call sp_addDivisionField(@leagueName, @u12, @facilityName, 'L2');
call sp_addDivisionField(@leagueName, @u14, @facilityName, 'L2');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, 'L2');
call sp_addDivisionField(@leagueName, @u11, @facilityName, 'U1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, 'U1');
call sp_addDivisionField(@leagueName, @u14, @facilityName, 'U1');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, 'U1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, 'U2');
call sp_addDivisionField(@leagueName, @u12, @facilityName, 'U2');
call sp_addDivisionField(@leagueName, @u14, @facilityName, 'U2');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, 'U2');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1A');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1A');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '1A');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '1A');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1B');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1B');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '1B');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '1B');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2A');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2A');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '2A');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '2A');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2B');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2B');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '2B');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '2B');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '3A');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '3A');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '3A');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '3A');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '3B');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '3B');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '3B');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '3B');
call sp_addFieldAvailability(@leagueName, @facilityName, 'L1', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'L2', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'U1', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'U2', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '1A', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '1B', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2A', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2B', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '3A', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '3B', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);

set @facilityName = 'Cold Springs School';
call sp_addFacility(@leagueName, @facilityName, '2243 Sycamore Canyon Rd', '', @montecito, 'CA', '93108', @usa,
    'Coral Godlis', 'cgodlis@coldspringschool.net', '(805) 969-2678 ext. 138', 'ColdSpringsElementary_zpsd4082dc1.jpg', 0, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @montecito);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u5, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '2');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Montecito Union School';
call sp_addFacility(@leagueName, @facilityName, '385 San Ysidro Rd', '', @montecito, 'CA', '93108', @usa,
    'Ryan Gleason', 'rgleason@montecitou.org', '(805) 969-3249 x410', 'MontecitoUnionElementary_zps47440eb2.jpg', 0, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @montecito);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addField(@leagueName, @facilityName, '3', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '3');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '3', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Manning Park';
call sp_addFacility(@leagueName, @facilityName, 'San Ysidro and Santa Rosa Ln', '', @montecito, 'CA', '93108', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'ManningPark_zpse7107a5a.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @montecito);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Andamar Open Space';
call sp_addFacility(@leagueName, @facilityName, 'Andamar Way off Dara Dr', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'AndamarOpenSpace.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Bella Vista Open Space';
call sp_addFacility(@leagueName, @facilityName, '7383 Mirano Dr', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'BellaVistaOpenSpace.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Berkely Park';
call sp_addFacility(@leagueName, @facilityName, 'Berkeley Rd', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'BerkeleyPark.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Evergreen Park';
call sp_addFacility(@leagueName, @facilityName, 'Brandon Dr & Evergreen', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'EvergreenLearningCenter.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Girsh Park';
call sp_addFacility(@leagueName, @facilityName, '7050 Phelps Rd', '', @goleta, 'CA', '93117', @usa,
    'Ryan Harrington', 'rharrington@girshpark.org', '', 'GirshParkFields_zps37ac68d3.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, 'A', 1);
call sp_addField(@leagueName, @facilityName, 'B', 1);
call sp_addField(@leagueName, @facilityName, 'C', 1);
call sp_addField(@leagueName, @facilityName, 'D', 1);
call sp_addField(@leagueName, @facilityName, 'E', 1);
call sp_addField(@leagueName, @facilityName, 'F', 1);
call sp_addField(@leagueName, @facilityName, 'G', 1);
call sp_addField(@leagueName, @facilityName, 'H', 1);
call sp_addField(@leagueName, @facilityName, 'I', 1);
call sp_addField(@leagueName, @facilityName, 'J', 1);
call sp_addField(@leagueName, @facilityName, 'K', 0);
call sp_addField(@leagueName, @facilityName, 'L', 0);
call sp_addField(@leagueName, @facilityName, 'M', 1);
call sp_addField(@leagueName, @facilityName, 'N', 1);
call sp_addField(@leagueName, @facilityName, 'O', 1);
call sp_addField(@leagueName, @facilityName, 'P', 1);
call sp_addField(@leagueName, @facilityName, 'Q', 1);
call sp_addField(@leagueName, @facilityName, 'R', 1);
call sp_addDivisionField(@leagueName, @u11, @facilityName, 'A');
call sp_addDivisionField(@leagueName, @u12, @facilityName, 'A');
call sp_addDivisionField(@leagueName, @u14, @facilityName, 'A');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, 'A');
call sp_addDivisionField(@leagueName, @u11, @facilityName, 'B');
call sp_addDivisionField(@leagueName, @u12, @facilityName, 'B');
call sp_addDivisionField(@leagueName, @u14, @facilityName, 'B');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, 'B');
call sp_addDivisionField(@leagueName, @u11, @facilityName, 'C');
call sp_addDivisionField(@leagueName, @u12, @facilityName, 'C');
call sp_addDivisionField(@leagueName, @u14, @facilityName, 'C');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, 'C');
call sp_addDivisionField(@leagueName, @u5, @facilityName, 'D');
call sp_addDivisionField(@leagueName, @u6, @facilityName, 'D');
call sp_addDivisionField(@leagueName, @u5, @facilityName, 'E');
call sp_addDivisionField(@leagueName, @u6, @facilityName, 'E');
call sp_addDivisionField(@leagueName, @u5, @facilityName, 'F');
call sp_addDivisionField(@leagueName, @u6, @facilityName, 'F');
call sp_addDivisionField(@leagueName, @u7, @facilityName, 'G');
call sp_addDivisionField(@leagueName, @u8, @facilityName, 'G');
call sp_addDivisionField(@leagueName, @u9, @facilityName, 'G');
call sp_addDivisionField(@leagueName, @u10, @facilityName, 'G');
call sp_addDivisionField(@leagueName, @u7, @facilityName, 'H');
call sp_addDivisionField(@leagueName, @u8, @facilityName, 'H');
call sp_addDivisionField(@leagueName, @u9, @facilityName, 'H');
call sp_addDivisionField(@leagueName, @u10, @facilityName, 'H');
call sp_addDivisionField(@leagueName, @u7, @facilityName, 'I');
call sp_addDivisionField(@leagueName, @u8, @facilityName, 'I');
call sp_addDivisionField(@leagueName, @u9, @facilityName, 'I');
call sp_addDivisionField(@leagueName, @u10, @facilityName, 'I');
call sp_addDivisionField(@leagueName, @u7, @facilityName, 'J');
call sp_addDivisionField(@leagueName, @u8, @facilityName, 'J');
call sp_addDivisionField(@leagueName, @u9, @facilityName, 'J');
call sp_addDivisionField(@leagueName, @u10, @facilityName, 'J');
call sp_addDivisionField(@leagueName, @u7, @facilityName, 'K');
call sp_addDivisionField(@leagueName, @u8, @facilityName, 'K');
call sp_addDivisionField(@leagueName, @u9, @facilityName, 'K');
call sp_addDivisionField(@leagueName, @u10, @facilityName, 'K');
call sp_addDivisionField(@leagueName, @u7, @facilityName, 'L');
call sp_addDivisionField(@leagueName, @u8, @facilityName, 'L');
call sp_addDivisionField(@leagueName, @u9, @facilityName, 'L');
call sp_addDivisionField(@leagueName, @u10, @facilityName, 'L');
call sp_addDivisionField(@leagueName, @u7, @facilityName, 'M');
call sp_addDivisionField(@leagueName, @u8, @facilityName, 'M');
call sp_addDivisionField(@leagueName, @u9, @facilityName, 'M');
call sp_addDivisionField(@leagueName, @u10, @facilityName, 'M');
call sp_addDivisionField(@leagueName, @u7, @facilityName, 'N');
call sp_addDivisionField(@leagueName, @u8, @facilityName, 'N');
call sp_addDivisionField(@leagueName, @u9, @facilityName, 'N');
call sp_addDivisionField(@leagueName, @u10, @facilityName, 'N');
call sp_addDivisionField(@leagueName, @u7, @facilityName, 'O');
call sp_addDivisionField(@leagueName, @u8, @facilityName, 'O');
call sp_addDivisionField(@leagueName, @u9, @facilityName, 'O');
call sp_addDivisionField(@leagueName, @u10, @facilityName, 'O');
call sp_addDivisionField(@leagueName, @u7, @facilityName, 'P');
call sp_addDivisionField(@leagueName, @u8, @facilityName, 'P');
call sp_addDivisionField(@leagueName, @u9, @facilityName, 'P');
call sp_addDivisionField(@leagueName, @u10, @facilityName, 'P');
call sp_addDivisionField(@leagueName, @u7, @facilityName, 'Q');
call sp_addDivisionField(@leagueName, @u8, @facilityName, 'Q');
call sp_addDivisionField(@leagueName, @u9, @facilityName, 'Q');
call sp_addDivisionField(@leagueName, @u10, @facilityName, 'Q');
call sp_addDivisionField(@leagueName, @u7, @facilityName, 'R');
call sp_addDivisionField(@leagueName, @u8, @facilityName, 'R');
call sp_addDivisionField(@leagueName, @u9, @facilityName, 'R');
call sp_addDivisionField(@leagueName, @u10, @facilityName, 'R');
call sp_addFieldAvailability(@leagueName, @facilityName, 'A', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'B', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'C', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'D', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'E', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'F', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'G', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'H', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'I', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'J', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'K', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'L', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'M', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'N', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'O', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'P', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'Q', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'R', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Lassen Open Space';
call sp_addFacility(@leagueName, @facilityName, 'Lassen & San Simeon', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'LassenOpenSpace.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Patterson Open Space';
call sp_addFacility(@leagueName, @facilityName, 'University and Ribera Dr.', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'PattersonOpenSpace_zpsd52e70e2.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u5, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '2');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Rhoads Park';
call sp_addFacility(@leagueName, @facilityName, '5010 Rhoads Ave.', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'RhoadsParkFields_zps60af914a.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addField(@leagueName, @facilityName, '3', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u5, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u5, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '3');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '3', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'San Miguel Open Space';
call sp_addFacility(@leagueName, @facilityName, '7900 block of Rio Vista Dr.', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'SanMiguelOpenSpace.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Stow Grove Park';
call sp_addFacility(@leagueName, @facilityName, 'La Patera & Cathedral Oaks', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'StowGrovePark_Fields_zps94acaaeb.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addField(@leagueName, @facilityName, '3', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '3');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '3', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Stow Canyon Open Space';
call sp_addFacility(@leagueName, @facilityName, 'Valdez Ave & Muirfield Dr', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'StowCanyonOpenSpace.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Thunderbird Open Space';
call sp_addFacility(@leagueName, @facilityName, 'Walnut and San Juan', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'ThunderbirdOpenSpaceFields_zps0ff7b6a6.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u5, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '2');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Tuckers Grove Park';
call sp_addFacility(@leagueName, @facilityName, 'Foothill Road and Turnpike', '', @goleta, 'CA', '93111', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'TuckersGroveParkFields_zpsc6e0bf12.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addFacilityLocation(@leagueName, @facilityName, @noleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addField(@leagueName, @facilityName, '3', 1);
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '3');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '3', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Univercity Circle Open Space';
call sp_addFacility(@leagueName, @facilityName, 'Merida Dr', '', @goleta, 'CA', '93111', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'UnivercityOpenSpaceFields_zpsc1fe7624.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u5, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '2');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Winchester Open Space';
call sp_addFacility(@leagueName, @facilityName, '7500 block of Calle Real Rd.', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'WinchesterOpenSpace.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Brandon Elementary School';
call sp_addFacility(@leagueName, @facilityName, '195 Brandon Dr', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'BrandonElementarySchool.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addDivisionField(@leagueName, @u5, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u6, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u7, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u8, @facilityName, '1');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Ellwood Elementary School';
call sp_addFacility(@leagueName, @facilityName, '7686 Hollister Ave.', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'EllwoodElementaryShoolFields_zps0412a77f.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Isla Vista Elementary School';
call sp_addFacility(@leagueName, @facilityName, '6875 El Colegio Rd', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'IslaVistaElementarySchoolFields_zpscb1ed888.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 0);
call sp_addField(@leagueName, @facilityName, '2', 0);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Mountain View Elementary School';
call sp_addFacility(@leagueName, @facilityName, '5465 Queen Ann Lane', '', @goleta, 'CA', '93111', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'MountainViewElementaryFields_zpsdc6bb75a.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1A', 1);
call sp_addField(@leagueName, @facilityName, '1B', 1);
call sp_addField(@leagueName, @facilityName, '1C', 1);
call sp_addField(@leagueName, @facilityName, '1D', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1A');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1A');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1A');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1A');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1B');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1B');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1B');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1B');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1C');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1C');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1C');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1C');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1D');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1D');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1D');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1D');
call sp_addFieldAvailability(@leagueName, @facilityName, '1A', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '1B', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '1C', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '1D', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'El Camino Elementary School';
call sp_addFacility(@leagueName, @facilityName, '5020 San Simeion Dr', '', @goleta, 'CA', '93111', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'ElCaminoElementarySchool_zps797e19e3.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addFacilityLocation(@leagueName, @facilityName, @noleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '2');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Foothill Elementary School';
call sp_addFacility(@leagueName, @facilityName, '711 Ribera Dr', '', @goleta, 'CA', '93111', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'FoothillElementaryFields_zps2d35de55.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addFacilityLocation(@leagueName, @facilityName, @noleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addField(@leagueName, @facilityName, '3', 1);
call sp_addField(@leagueName, @facilityName, 'K', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u5, @facilityName, 'K');
call sp_addDivisionField(@leagueName, @u6, @facilityName, 'K');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '3', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'K', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Hollister Elementary School';
call sp_addFacility(@leagueName, @facilityName, '4950 Anita Lane', '', @goleta, 'CA', '93111', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'HollisterElementarySchool.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addFacilityLocation(@leagueName, @facilityName, @noleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addField(@leagueName, @facilityName, '3', 1);
call sp_addField(@leagueName, @facilityName, 'K', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '3');
call sp_addDivisionField(@leagueName, @u5, @facilityName, 'K');
call sp_addDivisionField(@leagueName, @u6, @facilityName, 'K');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '3', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'K', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Kellogg Elementary School';
call sp_addFacility(@leagueName, @facilityName, '475 Cambridge Dr', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'KellogElementaryFields_zps40a20dad.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addDivisionField(@leagueName, @u9, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u9, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u10, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '2');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'La Patera Elementary School';
call sp_addFacility(@leagueName, @facilityName, '555 North La Patera Lane', '', @goleta, 'CA', '93117', @usa,
    'contactName', 'contactEmail', 'contactPhone', 'LaPateraElementaryFields_zpsfe3566bc.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @goleta);
call sp_addField(@leagueName, @facilityName, '1', 1);
call sp_addField(@leagueName, @facilityName, '2', 1);
call sp_addField(@leagueName, @facilityName, 'K', 1);
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '1');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '2');
call sp_addDivisionField(@leagueName, @u5, @facilityName, 'K');
call sp_addDivisionField(@leagueName, @u6, @facilityName, 'K');
call sp_addFieldAvailability(@leagueName, @facilityName, '1', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2', @startDate, @endDate, @startTime, @endTime, @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, 'K', @startDate, @endDate, @startTime, @endTime, @practiceDays);

set @facilityName = 'Santa Barbara Junior High School';
call sp_addFacility(@leagueName, @facilityName, '721 East Cota Street', '', @goleta, 'CA', '93103', @usa,
    'Natalie Alvarado', 'nalvarado@sbunified.org', '805.963.4338 x6305', 'SantaBarbaraJuniorHigh.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1A', 0);
call sp_addField(@leagueName, @facilityName, '1B', 0);
call sp_addField(@leagueName, @facilityName, '2A', 0);
call sp_addField(@leagueName, @facilityName, '2B', 0);
call sp_addField(@leagueName, @facilityName, '3A', 0);
call sp_addField(@leagueName, @facilityName, '3B', 0);
call sp_addDivisionField(@leagueName, @u14, @facilityName, '1A');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '1A');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '1B');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '1B');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '2A');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '2A');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '2B');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '2B');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '3A');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '3A');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '3B');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '3B');
call sp_addFieldAvailability(@leagueName, @facilityName, '1A', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '1B', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2A', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2B', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '3A', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '3B', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);

set @facilityName = 'La Colina Junior High School';
call sp_addFacility(@leagueName, @facilityName, '4025 Foothill Road', '', @goleta, 'CA', '93110', @usa,
    'Natalie Alvarado', 'nalvarado@sbunified.org', '805.963.4338 x6305', 'LaColinaJuniorHigh.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addFacilityLocation(@leagueName, @facilityName, @noleta);
call sp_addField(@leagueName, @facilityName, '1A', 0);
call sp_addField(@leagueName, @facilityName, '1B', 0);
call sp_addField(@leagueName, @facilityName, '2A', 0);
call sp_addField(@leagueName, @facilityName, '2B', 0);
call sp_addField(@leagueName, @facilityName, '3A', 0);
call sp_addField(@leagueName, @facilityName, '3B', 0);
call sp_addDivisionField(@leagueName, @u14, @facilityName, '1A');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '1A');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '1B');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '1B');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '2A');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '2A');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '2B');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '2B');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '3A');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '3A');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '3B');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '3B');
call sp_addFieldAvailability(@leagueName, @facilityName, '1A', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '1B', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2A', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2B', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '3A', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '3B', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);

set @facilityName = 'UCSB Storke Field';
call sp_addFacility(@leagueName, @facilityName, '', '', @santaBarbara, 'CA', '93106', @usa,
    'Celia Elliott', 'Celia.Elliott@essr.ucsb.edu', '', 'UCSBStorkeField.jpg', 1, 1);
call sp_addFacilityLocation(@leagueName, @facilityName, @santaBarbara);
call sp_addField(@leagueName, @facilityName, '1A', 0);
call sp_addField(@leagueName, @facilityName, '1B', 0);
call sp_addField(@leagueName, @facilityName, '2A', 0);
call sp_addField(@leagueName, @facilityName, '2B', 0);
call sp_addField(@leagueName, @facilityName, '3A', 0);
call sp_addField(@leagueName, @facilityName, '3B', 0);
call sp_addField(@leagueName, @facilityName, '4A', 0);
call sp_addField(@leagueName, @facilityName, '4B', 0);
call sp_addField(@leagueName, @facilityName, '5A', 1);
call sp_addField(@leagueName, @facilityName, '5B', 1);
call sp_addField(@leagueName, @facilityName, '6A', 0);
call sp_addField(@leagueName, @facilityName, '6B', 0);
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1A');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1A');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '1A');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '1A');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '1B');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '1B');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '1B');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '1B');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2A');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2A');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '2A');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '2A');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '2B');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '2B');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '2B');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '2B');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '3A');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '3A');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '3A');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '3A');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '3B');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '3B');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '3B');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '3B');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '4A');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '4A');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '4A');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '4A');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '4B');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '4B');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '4B');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '4B');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '5A');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '5A');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '5A');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '5A');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '5B');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '5B');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '5B');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '5B');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '6A');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '6A');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '6A');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '6A');
call sp_addDivisionField(@leagueName, @u11, @facilityName, '6B');
call sp_addDivisionField(@leagueName, @u12, @facilityName, '6B');
call sp_addDivisionField(@leagueName, @u14, @facilityName, '6B');
call sp_addDivisionField(@leagueName, @u16_19, @facilityName, '6B');
call sp_addFieldAvailability(@leagueName, @facilityName, '1A', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '1B', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2A', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '2B', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '3A', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '3B', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '4A', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '4B', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '5A', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '5B', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '6A', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);
call sp_addFieldAvailability(@leagueName, @facilityName, '6B', @startDate, @endDate, '04:30:00', '06:00:00', @practiceDays);

