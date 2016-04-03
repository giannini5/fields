drop procedure if exists sp_addFacilityLocation;

delimiter $$

create procedure sp_addFacilityLocation(in _leagueName   varchar(60),
                                        in _facilityName varchar(60),
                                        in _locationName varchar(60))
BEGIN
    declare l_leagueId   bigInt default NULL;
    declare l_facilityId bigInt default NULL;
    declare l_locationId bigInt default NULL;

    select l.id into l_leagueId from league as l where l.name = _leagueName;
    if (l_leagueId is NULL) then
        set @errorMessage = concat("ABORT: league not found for : ", _leagueName);
        SIGNAL SQLSTATE '90000' set MESSAGE_TEXT = @errorMessage;
    end if;

    select l.id into l_locationId from location as l where l.leagueId = l_leagueId and l.name = _locationName;
    if (l_locationId is NULL) then
        set @errorMessage = concat("ABORT: location not found for : ", _locationName);
        SIGNAL SQLSTATE '90000' set MESSAGE_TEXT = @errorMessage;
    end if;

    select f.id into l_facilityId from facility as f where f.leagueId = l_leagueId and f.name = _facilityName;
    if (l_facilityId is NULL) then
        set @errorMessage = concat("ABORT: facility not found for : ", _facilityName);
        SIGNAL SQLSTATE '90000' set MESSAGE_TEXT = @errorMessage;
    end if;

    insert ignore into facilityLocation (
        facilityId,
        locationId)
    values (
        l_facilityId,
        l_locationId);
END $$

delimiter ;
