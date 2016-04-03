drop procedure if exists sp_addDivisionField;

delimiter $$

create procedure sp_addDivisionField(in leagueName   varchar(60),
                                     in divisionName varchar(60),
                                     in facilityName varchar(60),
                                     in fieldName    varchar(60))
BEGIN
    declare l_leagueId   bigInt default NULL;
    declare l_divisionId bigInt default NULL;
    declare l_facilityId bigInt default NULL;
    declare l_fieldId    bigInt default NULL;

    select l.id into l_leagueId from league as l where l.name = leagueName;
    if (l_leagueId is NULL) then
        set @errorMessage = concat("ABORT: league not found for : ", ifnull(leagueName, 'NULL'));
        SIGNAL SQLSTATE '90000' set MESSAGE_TEXT = @errorMessage;
    end if;

    select d.id into l_divisionId from division as d where d.leagueId = l_leagueId and d.name = divisionName;
    if (l_divisionId is NULL) then
        set @errorMessage = concat("ABORT: division not found for : ", ifnull(divisionName, 'NULL'));
        SIGNAL SQLSTATE '90000' set MESSAGE_TEXT = @errorMessage;
    end if;

    select f.id into l_facilityId from facility as f where f.leagueId = l_leagueId and f.name = facilityName;
    if (l_facilityId is NULL) then
        set @errorMessage = concat("ABORT: facility not found for : ", ifnull(facilityName, 'NULL'));
        SIGNAL SQLSTATE '90000' set MESSAGE_TEXT = @errorMessage;
    end if;

    select f.id into l_fieldId from field as f where f.facilityId = l_facilityId and f.name = fieldName;
    if (l_fieldId is NULL) then
        set @errorMessage = concat("ABORT: field not found for : ", ifnull(fieldName, 'NULL'));
        SIGNAL SQLSTATE '90000' set MESSAGE_TEXT = @errorMessage;
    end if;

    insert ignore into divisionField(
        divisionId,
        facilityId,
        fieldId)
    values (
        l_divisionId,
        l_facilityId,
        l_fieldId);
END $$

delimiter ;
