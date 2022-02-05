package com.sipanduberadat.petugas.viewHolders

import android.content.Intent
import android.view.View
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.sipanduberadat.petugas.R
import com.sipanduberadat.petugas.activities.ReportDetailActivity
import com.sipanduberadat.petugas.models.Petugas
import com.sipanduberadat.petugas.models.ReportWrapper
import com.sipanduberadat.petugas.utils.Constants
import com.sipanduberadat.petugas.utils.getRelativeDateTimeString
import kotlinx.android.synthetic.main.layout_item_report.view.*

class ReportViewHolder(private val view: View): RecyclerView.ViewHolder(view) {
    fun onBindItem(wrapper: ReportWrapper, me: Petugas, showStatus: Boolean = true) {
        if (wrapper.report != null) {
            Glide.with(view.context).load(if (wrapper.report!!.jenis_pelaporan.emergency_status)
                wrapper.report!!.jenis_pelaporan.icon else wrapper.report!!.photo).centerCrop().into(view.photo)
            if (!wrapper.report!!.jenis_pelaporan.emergency_status)
                (view.photo.layoutParams as ViewGroup.MarginLayoutParams).setMargins(0, 0, 0, 0)
            view.emergency_status.text = if (wrapper.report!!.jenis_pelaporan.emergency_status) "Darurat" else
                "Keluhan"
            view.emergency_status.setChipBackgroundColorResource(if (wrapper.report!!.jenis_pelaporan.emergency_status)
                R.color.red_700 else R.color.blue)
            view.datetime.text = getRelativeDateTimeString(wrapper.report!!.time.time)
            view.title.text = if (wrapper.report!!.jenis_pelaporan.emergency_status)
                wrapper.report!!.jenis_pelaporan.name else wrapper.report!!.title
            view.reporter.text = wrapper.report!!.masyarakat.name
            view.report_name.text = wrapper.report!!.jenis_pelaporan.name
            view.report_status.visibility = if (showStatus) View.VISIBLE else View.GONE
            view.report_status.text = Constants.REPORT_STATUS_TITLES[wrapper.report!!.status + 1]
            view.report_status.setChipBackgroundColorResource(Constants.REPORT_STATUS_COLORS[wrapper.report!!.status + 1])

            view.container.setOnClickListener {
                val intent = Intent(view.context, ReportDetailActivity::class.java)
                intent.putExtra("ME", me)
                intent.putExtra("REPORT_ID", wrapper.report!!.id)
                intent.putExtra("REPORT_EMERGENCY_STATUS", wrapper.report!!.jenis_pelaporan.emergency_status)
                intent.putExtra("IS_REPORTER_KRAMA", true)
                view.context.startActivity(intent)
            }
        } else {
            Glide.with(view.context).load(if (wrapper.guestReport!!.jenis_pelaporan.emergency_status)
                wrapper.guestReport!!.jenis_pelaporan.icon else wrapper.guestReport!!.photo).centerCrop().into(view.photo)
            if (!wrapper.guestReport!!.jenis_pelaporan.emergency_status)
                (view.photo.layoutParams as ViewGroup.MarginLayoutParams).setMargins(0, 0, 0, 0)
            view.emergency_status.text = if (wrapper.guestReport!!.jenis_pelaporan.emergency_status) "Darurat" else
                "Keluhan"
            view.emergency_status.setChipBackgroundColorResource(if (wrapper.guestReport!!.jenis_pelaporan.emergency_status)
                R.color.red_700 else R.color.blue)
            view.datetime.text = getRelativeDateTimeString(wrapper.guestReport!!.time.time)
            view.title.text = if (wrapper.guestReport!!.jenis_pelaporan.emergency_status)
                wrapper.guestReport!!.jenis_pelaporan.name else wrapper.guestReport!!.title
            view.reporter.text = wrapper.guestReport!!.tamu.name
            view.report_name.text = wrapper.guestReport!!.jenis_pelaporan.name
            view.report_status.visibility = if (showStatus) View.VISIBLE else View.GONE
            view.report_status.text = Constants.REPORT_STATUS_TITLES[wrapper.guestReport!!.status + 1]
            view.report_status.setChipBackgroundColorResource(Constants.REPORT_STATUS_COLORS[wrapper.guestReport!!.status + 1])

            view.container.setOnClickListener {
                val intent = Intent(view.context, ReportDetailActivity::class.java)
                intent.putExtra("ME", me)
                intent.putExtra("REPORT_ID", wrapper.guestReport!!.id)
                intent.putExtra("REPORT_EMERGENCY_STATUS", wrapper.guestReport!!.jenis_pelaporan.emergency_status)
                intent.putExtra("IS_REPORTER_KRAMA", false)
                view.context.startActivity(intent)
            }
        }
    }
}