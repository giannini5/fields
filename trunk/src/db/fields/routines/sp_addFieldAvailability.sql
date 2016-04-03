drop procedure if exists sp_addFieldAvailability;

delimiter $$

create procedure sp_addFieldAvailability(in _leagueName   varchar(60),
                                         in _facilityName varchar(60),
                                         in _fieldName    varchar(60),
                                         in _startDate    date,
                                         in _endDate      date,
                                         in _startTime    time,
                                         in _endTime      time,
                                         in _daysOfWeek   char(8))
BEGIN
    declare l_leagueId   bigInt default NULL;
    declare l_facilityId bigInt default NULL;
    declare l_fieldId    bigInt default NULL;

    select l.id into l_leagueId from league as l where l.name = _leagueName;
    if (l_leagueId is NULL) then
        set @errorMessage = concat("ABORT: league not found for : ", _leagueName);
        SIGNAL SQLSTATE '90000' set MESSAGE_TEXT = @errorMessage;
    end if;

    select f.id into l_facilityId from facility as f where f.leagueId = l_leagueId and f.name = _facilityName;
    if (l_facilityId is NULL) then
        set @errorMessage = concat("ABORT: facility not found for : ", _facilityName);
        SIGNAL SQLSTATE '90000' set MESSAGE_TEXT = @errorMessage;
    end if;

    select f.id into l_fieldId from field as f where f.facilityId = l_facilityId and f.name = _fieldName;
    if (l_fieldId is NULL) then
        set @errorMessage = concat("ABORT: field not found for : ", _fieldName);
        SIGNAL SQLSTATE '90000' set MESSAGE_TEXT = @errorMessage;
    end if;

    insert into fieldAvailability(
        fieldId,
        startDate,
        endDate,
        startTime,
        endTime,
        daysOfWeek)
    values (
        l_fieldId,
        _startDate,
        _endDate,
        _startTime,
        _endTime,
        _daysOfWeek)
    on duplicate key update
        startDate  = _startDate,
        endDate    = _endDate,
        startTime  = _startTime,
        endTime    = _endTime,
        daysOfWeek = _daysOfWeek;
END $$

delimiter ;
