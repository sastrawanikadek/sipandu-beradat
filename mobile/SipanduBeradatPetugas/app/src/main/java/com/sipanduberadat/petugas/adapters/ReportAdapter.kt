package com.sipanduberadat.petugas.adapters

import android.content.Context
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.sipanduberadat.petugas.R
import com.sipanduberadat.petugas.models.Me
import com.sipanduberadat.petugas.models.Petugas
import com.sipanduberadat.petugas.models.ReportWrapper
import com.sipanduberadat.petugas.viewHolders.ReportViewHolder

class ReportAdapter(
        private val context: Context,
        private val items: List<ReportWrapper>,
        private val me: Petugas,
        private val showStatus: Boolean = true
): RecyclerView.Adapter<ReportViewHolder>() {
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ReportViewHolder {
        val view = LayoutInflater.from(context).inflate(R.layout.layout_item_report, parent,
                false)
        return ReportViewHolder(view)
    }

    override fun getItemCount(): Int {
        return items.size
    }

    override fun onBindViewHolder(holder: ReportViewHolder, position: Int) {
        return holder.onBindItem(items[position], me, showStatus)
    }
}