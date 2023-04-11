const schedule = require('node-schedule')
const { exec } = require('child_process')

new schedule.scheduleJob('* * * * *', function() {
    exec('php artisan schedule:run', function(error, stdout, stderr) {
        if (error) console.log(error)
        if (stderr) console.log(stderr)
        console.log(stdout)
    })
})