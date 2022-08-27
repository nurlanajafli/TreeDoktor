module.exports = {
  apps : [{
    name: "arbostar",
    script: "/app/socket/ws.js",
    watch: true,                  // enable watch & restart feature, if a file change in the folder or subfolder, your app will get reloaded
    exec_mode: "fork",            // mode to start your app, can be “cluster” or “fork”, default fork
//    exec_mode: "cluster",            // mode to start your app, can be “cluster” or “fork”, default fork
//    instances : "2",
    listen_timeout: 8000,         // time in ms before forcing a reload if app not listening
    kill_timeout: 1600,           // time in milliseconds before sending a final SIGKILL
    max_restarts: 10,             // number of consecutive unstable restarts (less than 1sec interval or custom time via min_uptime) before your app is considered errored and stop being restarted
    restart_delay: 4000,          // time to wait before restarting a crashed app (in milliseconds). defaults to 0.
    autorestart: true,            // true by default. if false, PM2 will not restart your app if it crashes or ends peacefully
    max_memory_restart: '210M',   // your app will be restarted if it exceeds the amount of memory specified. human-friendly format : it can be “10M”, “100K”, “2G” and so on…
    cwd: "/app/socket",
    combine_logs: true,
    // error_file: "/dev/stderr",
    // out_file: "/dev/stdout",
    // log_file: '/dev/stdout',
    // time: true
  }]
}
