package com.sipanduberadat.petugas.adapters

import android.content.Context
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.sipanduberadat.petugas.R
import com.sipanduberadat.petugas.models.Me
import com.sipanduberadat.petugas.models.Pelaporan
import com.sipanduberadat.petugas.viewHolders.HistoryViewHolder

class HistoryAdapter(
        private val context: Context,
        private val items: List<Pelaporan>,
        private val me: Me
): RecyclerView.Adapter<HistoryViewHolder>() {
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): HistoryViewHolder {
        val view = LayoutInflater.from(context).inflate(R.layout.layout_item_history, parent,
                false)
        return HistoryViewHolder(view)
    }

    override fun getItemCount(): Int {
        return items.size
    }

    override fun onBindViewHolder(holder: HistoryViewHolder, position: Int) {
        holder.onBindItem(items[position], me)
    }
}