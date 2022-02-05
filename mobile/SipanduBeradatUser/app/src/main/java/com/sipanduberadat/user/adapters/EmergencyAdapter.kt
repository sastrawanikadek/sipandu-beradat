package com.sipanduberadat.user.adapters

import android.content.Context
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.sipanduberadat.user.R
import com.sipanduberadat.user.models.JenisPelaporan
import com.sipanduberadat.user.viewHolders.EmergencyViewHolder
import com.sipanduberadat.user.viewModels.MainViewModel

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