package com.sipanduberadat.guest.adapters

import android.content.Context
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.models.JenisPelaporan
import com.sipanduberadat.guest.viewHolders.EmergencyViewHolder
import com.sipanduberadat.guest.viewModels.MainViewModel

class EmergencyAdapter(
    private val context: Context,
    private val items: List<JenisPelaporan>,
    private val viewModel: MainViewModel
): RecyclerView.Adapter<EmergencyViewHolder>() {
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): EmergencyViewHolder {
        val view = LayoutInflater.from(context).inflate(R.layout.layout_item_emergency, parent,
            false)
        return EmergencyViewHolder(view)
    }

    override fun getItemCount(): Int {
        return items.size
    }

    override fun onBindViewHolder(holder: EmergencyViewHolder, position: Int) {
        holder.onBindItem(items[position], viewModel, position)
    }
}