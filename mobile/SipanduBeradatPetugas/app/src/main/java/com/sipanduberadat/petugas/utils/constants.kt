package com.sipanduberadat.petugas.utils

import com.sipanduberadat.petugas.R

class Constants {
    companion object {
        const val BASE_URL = "https://sipanduberadat.com/api"
        val MONTH_NAMES: List<String> = listOf(
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember"
        )
        val REPORT_STATUS_COLORS: List<Int> = listOf(
                R.color.red_700,
                R.color.orange,
                R.color.blue,
                R.color.green
        )
        val REPORT_STATUS_TITLES: List<String> = listOf(
                "Tidak Valid",
                "Menunggu Validasi",
                "Sedang Diproses",
                "Selesai"
        )
        val REPORT_STATUS_DESCRIPTIONS: List<String> = listOf(
                "Laporan yang Anda ajukan tidak valid",
                "Mohon tunggu, pecalang sedang menuju ke lokasi untuk memvalidasi laporan",
                "Laporan sudah valid dan sedang diproses oleh petugas yang berwenang",
                "Laporan yang Anda ajukan telah selesai ditindaklanjuti oleh petugas"
        )
    }
}