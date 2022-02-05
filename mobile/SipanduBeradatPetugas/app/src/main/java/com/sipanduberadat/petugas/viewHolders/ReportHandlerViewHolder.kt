package com.sipanduberadat.petugas.viewHolders

import android.view.View
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.sipanduberadat.petugas.R
import com.sipanduberadat.petugas.models.PecalangPelaporan
import com.sipanduberadat.petugas.models.PetugasPelaporan
import kotlinx.android.synthetic.main.layout_item_report_handler.view.*

class ReportHandlerViewHolder(private val view: View): RecyclerView.ViewHolder(view) {
    fun onBindItemPecalang(report: PecalangPelaporan) {
        val statusTitles = listOf("Tidak Valid", "Sedang Berangkat", "Valid")
        val statusColors = listOf(R.color.red_700, R.color.blue, R.color.green)

        Glide.with(view.context).load(report.pecalang.masyarakat.avatar).centerCrop().into(view.avatar)
        view.name.text = report.pecalang.masyarakat.name
        view.status.text = statusTitles[report.status + 1]
        view.status.setChipBackgroundColorResource(statusColors[report.status + 1])

        if (report.photo != null) {
            view.empty_photo.visibility = View.GONE
            Glide.with(view.context).load(report.photo).centerCrop().into(view.photo)
            view.photo.visibility = View.VISIBLE
        }
    }

    fun onBindItemPetugas(report: PetugasPelaporan) {
        val statusTitles = listOf("Sedang Berangkat", "Selesai")
        val statusColors = listOf(R.color.blue, R.color.green)

        Glide.with(view.context).load(report.petugas.avatar).centerCrop().into(view.avatar)
        view.name.text = report.petugas.name
        view.status.text = statusTitles[report.status]
        view.status.setChipBackgroundColorResource(statusColors[report.status])

        if (report.photo != null) {
            view.empty_photo.visibility = View.GONE
            Glide.with(view.context).load(report.photo).centerCrop().into(view.photo)
            view.photo.visibility = View.VISIBLE
        }
    }
}