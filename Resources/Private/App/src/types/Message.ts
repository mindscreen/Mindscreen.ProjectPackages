enum MessageSeverity {
    Debug = 1,
    Notice = 2,
    Warning = 3,
    Error = 4,
}

type Message = {
    title: string,
    code: string,
    message: string,
    severity: MessageSeverity,
};

export { Message, MessageSeverity };
