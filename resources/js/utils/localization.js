export function formatDate(value) {
    if (!value) return ''
    // Calendar dates have no timezone; parse their components to avoid shifting a day.
    const text = String(value)
    const date = /^\d{4}-\d{2}-\d{2}$/.test(text)
        ? new Date(`${text}T12:00:00-05:00`) : new Date(value)
    return date.toLocaleDateString('en-US', {
        timeZone: 'America/New_York', month: '2-digit', day: '2-digit', year: 'numeric'
    })
}

export function todayInNewYork() {
    const parts = new Intl.DateTimeFormat('en-US', {
        timeZone: 'America/New_York', year: 'numeric', month: '2-digit', day: '2-digit'
    }).formatToParts(new Date())
    const get = (type) => parts.find(part => part.type === type).value
    return `${get('year')}-${get('month')}-${get('day')}`
}
