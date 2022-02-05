package com.sipanduberadat.guest.utils

import com.sipanduberadat.guest.R

class Constants {
    companion object {
        const val BASE_URL = "https://sipanduberadat.com/api"
        val MONTH_NAMES: List<String> = listOf(
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December"
        )
        val REPORT_STATUS_COLORS: List<Int> = listOf(
                R.color.red_700,
                R.color.orange,
                R.color.blue,
                R.color.green
        )
        val REPORT_STATUS_TITLES: List<String> = listOf(
                "Invalid",
                "Waiting for Validation",
                "On Progress",
                "Completed"
        )
        val REPORT_STATUS_DESCRIPTIONS: List<String> = listOf(
                "Your report was invalid",
                "Please wait, Pecalang is on the way to you to validate your report",
                "The report is valid and is being processed by authorized officers",
                "The report you submitted has been completed"
        )
    }
}