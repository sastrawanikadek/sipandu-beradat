package com.sipanduberadat.petugas.adapters

import android.content.Context
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.sipanduberadat.petugas.R
import com.sipanduberadat.petugas.models.PecalangPelaporan
import com.sipanduberadat.petugas.models.PetugasPelaporan
import com.sipanduberadat.petugas.viewHolders.ReportHandlerViewHolder

class ReportHandlerAdapter(
        private val context: Context,
        private val pecalangReports: List<PecalangPelaporan>?,
        private val petugasReports: List<PetugasPelaporan>?
): RecyclerView.Adapter<ReportHandlerViewHolder>() {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ReportHandlerViewHolder {
        val view = LayoutInflater.from(context).inflate(R.layout.layout_item_report_handler, parent,
                false)
        return ReportHandlerViewHolder(view)
    }

    override fun getItemCount(): Int {
        return pecalangReports?.size ?: petugasReports!!.size
    }

    override fun onBindViewHolder(holder: ReportHandlerViewHolder, position: Int) {
        if (pecalangReports != null) holder.onBindItemPecalang(pecalangReports[position]) else
            holder.onBindItemPetugas(petugasReports!![position])
    }
}