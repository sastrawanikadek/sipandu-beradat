package com.sipanduberadat.user.adapters

import android.content.Context
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.sipanduberadat.user.R
import com.sipanduberadat.user.models.Me
import com.sipanduberadat.user.models.Pelaporan
import com.sipanduberadat.user.models.ReportWrapper
import com.sipanduberadat.user.viewHolders.ReportViewHolder

class ReportAdapter(
        private val context: Context,
        private val items: List<ReportWrapper>,
        private val me: Me,
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