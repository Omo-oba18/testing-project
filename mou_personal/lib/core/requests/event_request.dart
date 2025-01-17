class EventRequest {
  int? id;
  String? title;
  DateTime? startDate;
  DateTime? endDate;
  String? comment;
  String? repeat;
  String? alarm;
  String? place;
  int? busyMode;
  List<int>? userIds;

  EventRequest({
    this.id,
    this.title,
    this.startDate,
    this.endDate,
    this.comment,
    this.repeat,
    this.alarm,
    this.place,
    this.busyMode,
    this.userIds,
  });
}
