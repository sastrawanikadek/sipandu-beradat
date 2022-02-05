package com.sipanduberadat.guest.viewHolders

import android.content.Intent
import android.view.View
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.activities.ReportDetailActivity
import com.sipanduberadat.guest.models.PelaporanTamu
import com.sipanduberadat.guest.utils.Constants
import com.sipanduberadat.guest.utils.getRelativeDateTimeString
import kotlinx.android.synthetic.main.layout_item_history.view.*

class HistoryViewHolder(private val view: View): RecyclerView.ViewHolder(view) {
    fun onBindItem(history: PelaporanTamu) {
        val location = "Desa Adat ${history.desa_adat.name}"

        Glide.with(view.context).load(if (history.jenis_pelaporan.emergency_status)
            history.jenis_pelaporan.icon else history.photo).centerCrop().into(view.photo)
        if (!history.jenis_pelaporan.emergency_status)
            (view.photo.layoutParams as ViewGroup.MarginLayoutParams).setMargins(0, 0, 0, 0)
        view.emergency_status.text = if (history.jenis_pelaporan.emergency_status) "Emergency" else
            "Complaint"
        view.emergency_status.setChipBackgroundColorResource(if (history.jenis_pelaporan.emergency_status)
            R.color.red_700 else R.color.blue)
        view.datetime.text = getRelativeDateTimeString(history.time.time)
        view.title.text = if (history.jenis_pelaporan.emergency_status)
            history.jenis_pelaporan.name else history.title
        view.location.text = location
        view.report_status.text = Constants.REPORT_STATUS_TITLES[history.status + 1]
        view.report_status.setChipBackgroundColorResource(Constants.REPORT_STATUS_COLORS[history.status + 1])

        view.container.setOnClickListener {
            val intent = Intent(view.context, ReportDetailActivity::class.java)
            intent.putExtra("REPORT_ID", history.id)
            intent.putExtra("REPORT_EMERGENCY_STATUS", history.jenis_pelaporan.emergency_status)
            intent.putExtra("IS_REPORTER_KRAMA", false)
            view.context.startActivity(intent)
        }
    }
}